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
        chrono: ""
    });

    const date = new Date();

    //Récuperation d'une facture en fonction de l'Id
    const fetchInvoice = async id => {
        try {
            const { amount, customer, sentAt, fee, chrono } = await invoicesAPI.findInvoice(id);
            setInvoice({ amount, customer, sentAt, fee, chrono });
        } catch (error) {
            toast.error("Impossible de charger la facture demandée !");
            history.replace("/invoices");
        }
    };

    const totalHT = invoice.amount + invoice.fee;


    //Chargement de la facture si besoin au chargement du composant ou au changement de l'Id
    useEffect(() => {
        fetchInvoice(id)

    }, [id]);

    return(
        <>
            <div className="d-flex justify-content-between invoice-head">
                <div>
                    <span className="strong">SCI MLK Mérignac </span><br/>
                    18/20 avenue de la somme <br/>
                    33700 Mérignac <br/>
                    <br/>
                    <span className="strong">NI TVA FR :</span> 499 517 365 <br/>
                    <span className="strong">Tel :</span> 06 79 76 31 54
                </div>
                <div>
                    <span className="strong">{invoice.customer.company}</span> <br/>
                    18 Avenue de la Somme <br/>
                    33700 Mérignac
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
                            <th scope="row">Période du mois {date.getMonth().toString()}</th>
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
            <div className="d-flex justify-content-around invoice-foot">
                <div>En votre aimable réglement</div>
                <div>Le gérant</div>

            </div>
        </>
    );
};

export default InvoiceShow;