import React, {useState, useEffect}  from 'react';
import Field from "../components/forms/Field";
import {Link} from "react-router-dom";
import customersAPI from "../services/customersAPI";

const CustomerPage = ({match, history}) => {
    const { id = "new" } = match.params;

    const [customer, setCustomer] = useState({
        lastname: "",
        firstname: "",
        email: "",
        company: ""
    });

    const [errors, setErrors] = useState({
        lastname: "",
        firstname: "",
        email: "",
        company: ""
    });

    const [edit, setEdit] = useState(false);

    //Récuperation d'un customer en fonction de l'Id
    const fetchCustomer = async id => {
        try {
            const { firstname, lastname, email, company } = await customersAPI.findCustomer(id);
            setCustomer({ firstname, lastname, email, company });
        } catch (error) {
            //TODO Flash message error
            history.replace("/customers");
        }
    };

    //Chargement du customer si besoin au chargement du composant ou au changement de l'Id
    useEffect(() => {
        if (id !== "new") {
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
            if (edit) {
                await customersAPI.editCustomer(id, customer);
                //TODO Flash message success
            } else {
                await customersAPI.createCustomer(customer);
                setErrors({});
                history.replace("/customers");

                //TODO Flash message success
            }
        } catch ({response}) {
            const { violations } = response.data;
            if (violations) {
                const apiErrors = {};
                violations.map(({propertyPath, message}) => {
                    apiErrors[propertyPath] = message;
                });
                setErrors(apiErrors);

                //TODO Flash message errors
            }
        }
    };

    return (
        <>
            {!edit && <h1>Création d'un client</h1> || <h1>Modification du client</h1>}

            <form onSubmit={handleSubmit}>
                <Field
                    name="lastname"
                    label="Nom de famille"
                    placeholder="Nom de famille du client"
                    value={customer.lastname}
                    onChange={handleChange}
                    error={errors.lastname}
                />
                <Field
                    name="firstname"
                    label="Prénom"
                    placeholder="Prénom du client"
                    value={customer.firstname}
                    onChange={handleChange}
                    error={errors.firstname}
                />
                <Field
                    name="email"
                    label="Email"
                    placeholder="Adresse email du client"
                    type="email"
                    value={customer.email}
                    onChange={handleChange}
                    error={errors.email}
                />
                <Field
                    name="company"
                    label="Entreprise"
                    placeholder="Entreprise du client"
                    value={customer.company}
                    onChange={handleChange}
                    error={errors.company}
                />

                <div className="form-group">
                    <button type="submit" className="btn btn-success">Enregistrer</button>
                    <Link to="/customers" className="btn btn-link">Retour à la liste</Link>
                </div>
            </form>
        </>

    );
};

export default CustomerPage;