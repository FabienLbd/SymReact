import React, {useState, useEffect} from 'react';
import Field from "../components/forms/Field";
import Select from "../components/forms/Select";
import {Link} from "react-router-dom";
import customersAPI from "../services/customersAPI";
import invoicesAPI from "../services/invoicesAPI";
import { toast } from "react-toastify";
import FormContentLoader from "../components/loaders/FormContentLoader";

const InvoicePage = ({history, match}) => {
    const {id ="new"} = match.params;

    const [invoice, setInvoice] = useState({
        amount: "",
        customer: "",
        status: "SENT"
    });

    const [customers, setCustomers] = useState([]);

    const [errors, setErrors] = useState({
        amount: "",
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
            toast.error("Impossible de charger les clients");
            history.replace("/invoices");
        }
    };

    //Récuperation d'une facture en fonction de l'Id
    const fetchInvoice = async id => {
        try {
            const { amount, status, customer } = await invoicesAPI.findInvoice(id);
            setInvoice({ amount, status, customer: customer.id });
            setLoading(false);
        } catch (error) {
            toast.error("Impossible de charger la facture demandée !");
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
            if (edit) {
                await invoicesAPI.editInvoice(id, invoice);
                toast.success("La facture a bien été modifiée !");
            } else {
                await invoicesAPI.createInvoice(invoice);
                setErrors({});
                toast.success("La facture a bien été enregistrée !");
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
                toast.error("Il y a des erreurs dans votre formulaire !");
            }
        }
    };

    return (
        <>
            {!edit && <h1>Création d'une facture</h1> || <h1>Modification de la facture</h1>}
            { loading && <FormContentLoader/> }
            { !loading && ( <form onSubmit={handleSubmit}>
                <Field
                    name="amount"
                    type="number"
                    placeholder="Montant de la facture"
                    label="Montant"
                    onChange={handleChange}
                    value={invoice.amount}
                    error={errors.amount}
                />
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
                <div className="form-group">
                    <button type="submit" className="btn btn-success">Enregistrer</button>
                    <Link to="/invoices" className="btn btn-link">Retour à la liste</Link>
                </div>
            </form>
            )}
        </>
    );
};

export default InvoicePage;