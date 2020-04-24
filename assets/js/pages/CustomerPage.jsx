import React, {useState, useEffect}  from 'react';
import Field from "../components/forms/Field";
import {Link} from "react-router-dom";
import customersAPI from "../services/customersAPI";
import { toast } from "react-toastify";
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import {faChevronLeft, faSave} from '@fortawesome/fontawesome-free-solid';
import SpinnerLoader from "../components/loaders/SpinnerLoader";

const CustomerPage = ({match, history}) => {
    const { id = "new" } = match.params;

    const [customer, setCustomer] = useState({
        lastname: "",
        firstname: "",
        email: "",
        address: "",
        postalCode: "",
        city: "",
        company: ""
    });

    const [errors, setErrors] = useState({
        lastname: "",
        firstname: "",
        email: "",
        address: "",
        postalCode: "",
        city: "",
        company: ""
    });

    const [edit, setEdit] = useState(false);
    const [loading, setLoading] = useState(false);

    //Récuperation d'un customer en fonction de l'Id
    const fetchCustomer = async id => {
        try {
            const { firstname, lastname, email, address, city, postalCode, company } = await customersAPI.findCustomer(id);
            setCustomer({ firstname, lastname, email, address, city, postalCode, company });
            setLoading(false);
        } catch (error) {
            toast.error("Oups! Le client n'a pas pu être chargé !");
            history.replace("/customers");
        }
    };

    //Chargement du customer si besoin au chargement du composant ou au changement de l'Id
    useEffect(() => {
        if (id !== "new") {
            setLoading(true);
            setEdit(true);
            fetchCustomer(id)
        }
    }, [id]);

    //Gestion du changement dans les inputs du formulaire
    const handleChange = ({currentTarget}) => {
        const {value, name} = currentTarget;
        setCustomer({...customer, [name]: value})
    };

    //Gestion de la soumission du formulaire
    const handleSubmit = async event => {
        event.preventDefault();
        try {
            setErrors({});
            if (edit) {
                await customersAPI.editCustomer(id, customer);
                toast.success("Super! Le client a bien été modifié !");
            } else {
                await customersAPI.createCustomer(customer);
                toast.success("Parfait! Le client a bien été enregistré !");
                history.replace("/customers");
            }
        } catch ({response}) {
            const { violations } = response.data;
            if (violations) {
                const apiErrors = {};
                violations.map(({propertyPath, message}) => {
                    apiErrors[propertyPath] = message;
                });
                setErrors(apiErrors);
                toast.error("Oups! Il y a des erreurs dans votre formulaire !");
            }
        }
    };

    return (
        <>
            <div className="w-75 mx-auto">
                {!edit && <h1>Création d'un client</h1> || <h1>Modification du client</h1>}
                { loading && <SpinnerLoader/> }

                { !loading && (
                    <form onSubmit={handleSubmit}>
                        <div className="row">
                            <div className="col-md-6">
                                <Field
                                    isRequired={true}
                                    name="lastname"
                                    label="Nom de famille"
                                    placeholder="Nom de famille du client"
                                    value={customer.lastname}
                                    onChange={handleChange}
                                    error={errors.lastname}
                                />
                            </div>
                            <div className="col-md-6">
                                <Field
                                    isRequired={true}
                                    name="firstname"
                                    label="Prénom"
                                    placeholder="Prénom du client"
                                    value={customer.firstname}
                                    onChange={handleChange}
                                    error={errors.firstname}
                                />
                            </div>
                        </div>
                        <div className="row">
                            <div className="col-md-6">
                                <Field
                                    isRequired={true}
                                    name="email"
                                    label="Email"
                                    placeholder="Adresse email du client"
                                    type="email"
                                    value={customer.email}
                                    onChange={handleChange}
                                    error={errors.email}
                                />
                            </div>
                            <div className="col-md-6">
                                <Field
                                    isRequired={true}
                                    name="address"
                                    label="Adresse"
                                    placeholder="Entrez votre adresse"
                                    type="text"
                                    value={customer.address}
                                    onChange={handleChange}
                                    error={errors.address}
                                />
                            </div>
                        </div>
                        <div className="row">
                            <div className="col-md-6">
                                <Field
                                    isRequired={true}
                                    name="postalCode"
                                    label="Code postal"
                                    placeholder="Entrez votre code postal"
                                    type="text"
                                    value={customer.postalCode}
                                    onChange={handleChange}
                                    error={errors.postalCode}
                                />
                            </div>
                            <div className="col-md-6">
                                <Field
                                    isRequired={true}
                                    name="city"
                                    label="Ville"
                                    placeholder="Entrez votre ville"
                                    type="text"
                                    value={customer.city}
                                    onChange={handleChange}
                                    error={errors.city}
                                />
                            </div>
                        </div>
                        <div className="row">
                            <div className="col-12">
                                <Field
                                    isRequired={true}
                                    name="company"
                                    label="Entreprise"
                                    placeholder="Entreprise du client"
                                    value={customer.company}
                                    onChange={handleChange}
                                    error={errors.company}
                                />
                            </div>
                        </div>
                        <div className="form-group d-flex justify-content-end">
                            <Link to="/customers" className="btn btn-link">
                                <FontAwesomeIcon icon={faChevronLeft} />
                                Retour à la liste
                            </Link>
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

export default CustomerPage;