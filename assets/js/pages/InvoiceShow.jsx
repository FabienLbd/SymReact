import React, {useState, useEffect} from 'react';
import invoicesAPI from "../services/invoicesAPI";
import {toast} from "react-toastify";
import Pdf from "react-to-pdf";
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import { faDownload } from '@fortawesome/fontawesome-free-solid';
import SpinnerLoader from "../components/loaders/SpinnerLoader";

const InvoiceShow = ({ match, history }) => {
    const { id } = match.params;
    const [loading, setLoading] = useState(true);
    const [invoice, setInvoice] = useState({
        amount: 0,
        customer: "",
        sentAt:"",
        fee: 0,
        chrono: "",
        user: "",
        feeReminder: 0,
        amountReminder: 0,
        isReminderInvoice: false,
        total: 0,
        totalHT: 0,
        tvaAmount: 0
    });

    const months = [
        "Janvier",
        "Février",
        "Mars",
        "Avril",
        "Mai",
        "Juin",
        "Juillet",
        "Août",
        "Septembre",
        "Octobre",
        "Novembre",
        "Décembre"
    ];

    //Gestion de la date de facturation
    const now = new Date();
    let invoiceDate;
    const year = now.getFullYear();
    let invoiceMonth = now.getMonth();

    if (now.getDate() > 25 && now.getDate() < 31) {
        invoiceMonth = now.getMonth() + 1;
        invoiceDate = new Date(year, invoiceMonth, 1);
    } else {
        invoiceDate = new Date(year, invoiceMonth, 1 );
    }

    //Gestion de l'extraction en pdf
    const ref = React.createRef();

    const fileName = `facture-${months[invoiceMonth]}-${invoice.customer.company}`;

    const onComplete = () => {
        toast.success("Ok! Le fichier pdf a bien été téléchargé !");
    };

    //Récuperation d'une facture en fonction de l'Id
    const fetchInvoice = async id => {
        try {
            const {
                amount,
                customer,
                sentAt,
                fee,
                chrono,
                user,
                amountReminder,
                feeReminder,
                isReminderInvoice,
                total,
                totalHT,
                tvaAmount
            } = await invoicesAPI.findInvoice(id);
            setInvoice({
                amount,
                customer,
                sentAt,
                fee,
                chrono,
                user,
                amountReminder,
                feeReminder,
                isReminderInvoice,
                total,
                totalHT,
                tvaAmount
            });
            setLoading(false);
        } catch (error) {
            toast.error("Oups! Impossible de charger la facture demandée !");
            history.replace("/invoices");
        }
    };

    //Chargement de la facture si besoin au chargement du composant ou au changement de l'Id
    useEffect(() => {
        fetchInvoice(id);

    }, [id]);

    return(
        <>
            <div className="mb-3 d-flex justify-content-around">
                <h2 className="my-0 align-self-center">Aperçu de la facture</h2>
                <Pdf targetRef={ref}
                     filename={fileName}
                     x={2} y={2}
                     onComplete={onComplete}>
                    {({ toPdf }) =>
                        <button className="btn btn-outline-danger" onClick={toPdf}>
                            <FontAwesomeIcon icon={faDownload} />
                            Télécharger en pdf
                        </button>}
                </Pdf>
            </div>
            { !loading && (
                <div className="frame mb-3">
                    <div ref={ref} className="invoice-show">
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
                                <p>{invoiceDate.toLocaleDateString()}</p>
                            </div>
                        </div>
                        <div className="pl-2">
                            <span className="strong">Objet</span> : Facture de loyer N° {invoice.chrono}
                        </div>
                        <div className="mt-3 mb-5 mx-auto">
                            <table className="table table-borderless table-striped table-invoice">
                                <tbody>
                                    <tr>
                                        <th scope="row">Période {months[invoiceMonth] + " " + year}</th>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <th scope="row">Montant des loyers HT</th>
                                        <td>{new Intl.NumberFormat("fr-FR", {style: "currency", currency: "EUR"}).format(invoice.amount)}</td>
                                    </tr>
                                    <tr className="border-bot">
                                        <th scope="row">Provisions charges HT</th>
                                        <td>{new Intl.NumberFormat("fr-FR", {style: "currency", currency: "EUR"}).format(invoice.fee)}</td>
                                    </tr>
                                    {invoice.isReminderInvoice &&
                                    <>
                                        <tr>
                                            <th scope="row">Rappel de loyer HT</th>
                                            <td>{new Intl.NumberFormat("fr-FR", {style: "currency", currency: "EUR"}).format(invoice.amountReminder)}</td>
                                        </tr>
                                        <tr className="border-bot">
                                            <th scope="row">Rappel de charges HT</th>
                                            <td>{new Intl.NumberFormat("fr-FR", {style: "currency", currency: "EUR"}).format(invoice.feeReminder)}</td>
                                        </tr>
                                    </>

                                    }
                                    <tr>
                                        <th scope="row">Total HT</th>
                                        <td>{new Intl.NumberFormat("fr-FR", {style: "currency", currency: "EUR"}).format(invoice.totalHT)}</td>
                                    </tr>
                                    <tr className="border-bot">
                                        <th scope="row">TVA à 20%</th>
                                        <td>{new Intl.NumberFormat("fr-FR", {style: "currency", currency: "EUR"}).format(invoice.tvaAmount)}</td>
                                    </tr>
                                    <tr>
                                        <th scope="row">Total du TTC</th>
                                        <td>{new Intl.NumberFormat("fr-FR", {style: "currency", currency: "EUR"}).format(invoice.total)}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div className="d-flex justify-content-around invoice-foot">
                            <div>En votre aimable réglement</div>
                            <div>Le gérant</div>
                        </div>
                    </div>
                </div>
            )}
            { loading && <SpinnerLoader/> }
        </>
    );
};

export default InvoiceShow;