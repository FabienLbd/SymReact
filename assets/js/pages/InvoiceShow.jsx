import React, {useState, useEffect} from 'react';
import invoicesAPI from "../services/invoicesAPI";
import {toast} from "react-toastify";

const InvoiceShow = ({ match, history }) => {
    const { id } = match.params;

    const [invoice, setInvoice] = useState({
        amount: "",
        customer: "",
        sentAt:"",
        fee: "",
        chrono: "",
        user: ""
    });

    const months = {
        1: "Janvier",
        2: "Février",
        3: "Mars",
        4: "Avril",
        5: "Mai",
        6: "Juin",
        7: "Juillet",
        8: "Août",
        9: "Septembre",
        10: "Octobre",
        11: "Novembre",
        12: "Décembre"
    };

    const date = new Date();
    date.setDate(1);

    const invoiceMonth = date.getMonth() + 1;

    //Récuperation d'une facture en fonction de l'Id
    const fetchInvoice = async id => {
        try {
            const { amount, customer, sentAt, fee, chrono, user } = await invoicesAPI.findInvoice(id);
            setInvoice({ amount, customer, sentAt, fee, chrono, user });
        } catch (error) {
            toast.error("Impossible de charger la facture demandée !");
            history.replace("/invoices");
        }
    };

    const totalHT = invoice.amount + invoice.fee;


    //Chargement de la facture si besoin au chargement du composant ou au changement de l'Id
    useEffect(() => {
        fetchInvoice(id);

    }, [id]);

    return(
        <>
            <div className="d-flex justify-content-between invoice-head">
                <div>
                    <span className="strong">{invoice.user.company}</span><br/>
                    {invoice.user.address}<br/>
                    {invoice.user.postalCode + " " +  invoice.user.city}<br/>
                    <br/>
                    <span className="strong">NI TVA FR :</span> {invoice.user.numTVA}<br/>
                    <span className="strong">Tel :</span> {invoice.user.phone}
                </div>
                <div>
                    <span className="strong">{invoice.customer.company}</span><br/>
                    {invoice.customer.address}<br/>
                    {invoice.customer.postalCode + " " + invoice.customer.city}
                </div>
                <div>
                    <p>Merignac le,</p>
                    <p>{date.toLocaleDateString()}</p>
                </div>
            </div>
            <div>
                <span className="strong">Objet</span> : Facture de loyer N° {invoice.chrono}
            </div>
            <div className="mt-3 mb-5">
                <table className="table table-borderless table-striped table-invoice">
                    <tbody>
                        <tr>
                            <th scope="row">Période du mois d' {months[invoiceMonth] + " " + date.getFullYear()}</th>
                            <td></td>
                        </tr>
                        <tr>
                            <th scope="row">Montant des loyers HT</th>
                            <td>{new Intl.NumberFormat("fr-FR", {style: "currency", currency: "EUR"}).format(invoice.amount)}</td>
                        </tr>
                        <tr className="border-bot">
                            <th scope="row">Provisions charges</th>
                            <td>{new Intl.NumberFormat("fr-FR", {style: "currency", currency: "EUR"}).format(invoice.fee)}</td>
                        </tr>
                        <tr>
                            <th scope="row">Total HT</th>
                            <td>{new Intl.NumberFormat("fr-FR", {style: "currency", currency: "EUR"}).format(totalHT)}</td>
                        </tr>
                        <tr className="border-bot">
                            <th scope="row">TVA à 20%</th>
                            <td>{new Intl.NumberFormat("fr-FR", {style: "currency", currency: "EUR"}).format(totalHT * 0.20)}</td>
                        </tr>
                        <tr>
                            <th scope="row">Total du TTC</th>
                            <td>{new Intl.NumberFormat("fr-FR", {style: "currency", currency: "EUR"}).format(totalHT + totalHT * 0.20)}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div className="d-flex justify-content-around invoice-foot mb-4">
                <div>En votre aimable réglement</div>
                <div>Le gérant</div>
            </div>
        </>
    );
};

export default InvoiceShow;