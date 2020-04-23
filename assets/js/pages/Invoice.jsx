import React, {useState, useEffect} from 'react';
import Field from "../components/forms/Field";
import Select from "../components/forms/Select";
import {Link} from "react-router-dom";
import customersAPI from "../services/customersAPI";
import invoicesAPI from "../services/invoicesAPI";
import { toast } from "react-toastify";
import {faSave, faChevronLeft} from "@fortawesome/fontawesome-free-solid";
import {FontAwesomeIcon} from "@fortawesome/react-fontawesome";
import SpinnerLoader from "../components/loaders/SpinnerLoader";


const InvoicePage = ({history, match}) => {
    const {id ="new"} = match.params;

    const [invoice, setInvoice] = useState({
        amount: "",
        fee: "",
        customer: "",
        status: "SENT"
    });

    const [customers, setCustomers] = useState([]);

    const [errors, setErrors] = useState({
        amount: "",
        fee: "",
        customer: "",
        status: ""
    });

    const [edit, setEdit] = useState(false);

    const [loading, setLoading] = useState(true);

    //Récuperation de la liste des customers
    const fetchCustomers = async () => {
        try {
            const data = await customersAPI.findAll();
            setCustomers(data);
            setLoading(false);

            if (!invoice.customer) setInvoice({...invoice, customer: data[0].id});
        } catch(error) {
            toast.error("Oups! Il semblerai qu'il y ai un problème pour charger les clients");
            history.replace("/invoices");
        }
    };

    //Récuperation d'une facture en fonction de l'Id
    const fetchInvoice = async id => {
        try {
            const { amount, status, customer, fee } = await invoicesAPI.findInvoice(id);
            setInvoice({ amount, status, customer: customer.id, fee });
            setLoading(false);
        } catch (error) {
            toast.error("Oh non! Impossible de charger la facture demandée !");
            history.replace("/invoices");
        }
    };

    //Récuperation de la liste des clients à chaque chargement du composant
    useEffect(() => {
        fetchCustomers();
    }, []);

    //Chargement de la facture si besoin au chargement du composant ou au changement de l'Id
    useEffect(() => {
        if (id !== "new") {
            setEdit(true);
            fetchInvoice(id)
        }
    }, [id]);

    //Gestion du changement dans les inputs du formulaire
    const handleChange = ({currentTarget}) => {
        const {value, name} = currentTarget;
        setInvoice({...invoice, [name]: value})
    };

    //Gestion de la soumission du formulaire
    const handleSubmit = async event => {
        event.preventDefault();
        try {
            setErrors({});
            if (edit) {
                await invoicesAPI.editInvoice(id, invoice);
                toast.success("Parfait! La facture a bien été modifiée !");
            } else {
                await invoicesAPI.createInvoice(invoice);
                toast.success("Ok! La facture a bien été enregistrée !");
                history.replace("/invoices");
            }
        } catch ({response}) {
            const { violations } = response.data;
            if (violations) {
                const apiErrors = {};
                violations.map(({propertyPath, message}) => {
                    apiErrors[propertyPath] = message;
                });
                setErrors(apiErrors);
                toast.error("Oups! Il semblerai qu'il y ai des erreurs dans votre formulaire !");
            }
        }
    };

    return (
        <>
            <div className="w-75 mx-auto">
                {!edit && <h1 className="mb-3">Création d'une facture</h1> ||
                <h1 className="mb-3">Modification de la facture</h1>}
                { loading && <SpinnerLoader/> }
                { !loading && (
                    <form onSubmit={handleSubmit}>
                        <div className="row">
                            <div className="col-6">
                                <Field
                                    isRequired={true}
                                    name="amount"
                                    type="number"
                                    placeholder="Entrer le montant"
                                    label="Montant du loyer HT"
                                    onChange={handleChange}
                                    value={invoice.amount}
                                    error={errors.amount}
                                />
                                <Field
                                    isRequired={true}
                                    name="fee"
                                    type="number"
                                    placeholder="Montant des charges"
                                    label="Montant des charges"
                                    onChange={handleChange}
                                    value={invoice.fee}
                                    error={errors.fee}
                                />
                            </div>
                            <div className="col-6">
                                <Select
                                    name="customer"
                                    label="Client"
                                    value={invoice.customer}
                                    error={errors.customer}
                                    onChange={handleChange}
                                >
                                    {customers.map(customer => (
                                        <option key={customer.id} value={customer.id}>
                                            {customer.firstname} {customer.lastname}
                                        </option>
                                    ))}
                                </Select>
                                <Select
                                    name="status"
                                    label="Statut"
                                    value={invoice.status}
                                    error={errors.status}
                                    onChange={handleChange}
                                >
                                    <option value="SENT">Envoyée</option>
                                    <option value="PAID">Payée</option>
                                    <option value="CANCELLED">Annulée</option>
                                </Select>
                            </div>
                        </div>
                        <div className="form-group d-flex justify-content-end">
                            <Link to="/invoices" className="btn btn-link">
                                <FontAwesomeIcon icon={faChevronLeft} />
                                Retour à la liste</Link>
                            <button type="submit" className="btn btn-outline-success">
                                <FontAwesomeIcon icon={faSave} />
                                Enregistrer
                            </button>
                        </div>
                    </form>
                )}
            </div>

        </>
    );
};

export default InvoicePage;