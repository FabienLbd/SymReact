import React, {useEffect, useState} from 'react';
import customersAPI from "../services/customersAPI";
import {toast} from "react-toastify";
import usersAPI from "../services/usersAPI";
import { USERS_API } from "../config";
import Field from "../components/forms/Field";
import axios from 'axios';
import {Link} from "react-router-dom";
import SpinnerLoader from "../components/loaders/SpinnerLoader";
import {faChevronLeft} from "@fortawesome/fontawesome-free-solid";
import {FontAwesomeIcon} from "@fortawesome/react-fontawesome";

const UserEdit = ({match, history}) => {
    const { id } = match.params;

    const [loading, setLoading] = useState(false);

    const [user, setUser] = useState({
        firstname: "",
        lastname: "",
        address: "",
        postalCode: "",
        city: "",
        phone: "",
        numTVA:"",
        company:"",
    });

    const [errors, setErrors] = useState({
        firstname: "",
        lastname: "",
        address: "",
        postalCode: "",
        city: "",
        phone: "",
        numTVA:"",
        company:"",
    });

    //Récuperation d'un customer en fonction de l'Id
    const fetchUser = async id => {
        try {
            const { firstname, lastname, address, city, postalCode, numTVA, company, phone } =
                await axios.get(USERS_API + "/" + id)
                           .then(response => response.data);
            setUser({ firstname, lastname, address, city, postalCode, numTVA, company, phone });
            setLoading(false);
        } catch (error) {
            toast.error("Oups! Il y a eu un problème !");
            history.replace("/users/" + id);
        }
    };

    //Chargement du customer si besoin au chargement du composant ou au changement de l'Id
    useEffect(() => {
        setLoading(true);
        fetchUser(id)
    }, [id]);

    //Gestion du changement dans les inputs du formulaire
    const handleChange = ({currentTarget}) => {
        const {value, name} = currentTarget;
        setUser({...user, [name]: value})
    };

    //Gestion de la soumission du formulaire
    const handleSubmit = async event => {
        event.preventDefault();
        try {
            setErrors({});
            await axios.put(USERS_API + "/" + id, user)
            toast.success("C'est bon! Votre profile a bien été modifié !");
            history.replace("/users/" + id)
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
            <h1 className="mb-3">Modification des informations</h1>
            { loading && <SpinnerLoader/> }
            { !loading && (
            <form onSubmit={handleSubmit}>
                <div className="row">
                    <div className="col-md-6">
                        <Field
                            isRequired={true}
                            name="firstname"
                            type="text"
                            placeholder="Votre prénom"
                            label="Prénom"
                            onChange={handleChange}
                            value={user.firstname}
                            error={errors.firstname}
                        />
                    </div>
                    <div className="col-md-6">
                        <Field
                            isRequired={true}
                            name="lastname"
                            type="text"
                            placeholder="Votre nom de famille"
                            label="Nom de famille"
                            onChange={handleChange}
                            value={user.lastname}
                            error={errors.lastname}
                        />
                    </div>
                </div>
                <div className="row">
                    <div className="col-md-6">
                        <Field
                            isRequired={true}
                            name="address"
                            type="text"
                            placeholder="Entrez votre adresse"
                            label="Adresse"
                            onChange={handleChange}
                            value={user.address}
                            error={errors.address}
                        />
                    </div>
                    <div className="col-md-6">
                        <Field
                            isRequired={true}
                            name="city"
                            type="text"
                            placeholder="Entrez votre ville"
                            label="Ville"
                            onChange={handleChange}
                            value={user.city}
                            error={errors.city}
                        />
                    </div>
                </div>
                <div className="row">
                    <div className="col-md-6">
                        <Field
                            isRequired={true}
                            name="postalCode"
                            type="text"
                            placeholder="Entrez votre code postal"
                            label="Code postal"
                            onChange={handleChange}
                            value={user.postalCode}
                            error={errors.postalCode}
                        />
                    </div>
                    <div className="col-md-6">
                        <Field
                            isRequired={true}
                            name="phone"
                            type="text"
                            placeholder="Entrez votre numéro de téléphone"
                            label="Téléphone"
                            onChange={handleChange}
                            value={user.phone}
                            error={errors.phone}
                        />
                    </div>
                </div>
                <div className="row">
                    <div className="col-md-6">
                        <Field
                            isRequired={true}
                            name="numTVA"
                            type="number"
                            placeholder="Entrez votre numéro de TVA"
                            label="Numéro de TVA"
                            onChange={handleChange}
                            value={user.numTVA}
                            error={errors.numTVA}
                        />
                    </div>
                    <div className="col-md-6">
                        <Field
                            isRequired={true}
                            name="company"
                            type="text"
                            placeholder="Entrez le nom de votre société"
                            label="Société"
                            onChange={handleChange}
                            value={user.company}
                            error={errors.company}
                        />
                    </div>
                </div>
                <div className="form-group d-flex justify-content-end">
                    <Link to={"/users/" + id} className="btn btn-link">
                        <FontAwesomeIcon icon={faChevronLeft} />
                        Retour au profile
                    </Link>
                    <button type="submit" className="btn btn-success">Enregistrer</button>
                </div>
            </form>
            )}
        </>
    );
};

export default UserEdit;