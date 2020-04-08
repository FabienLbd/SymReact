import React, {useState} from 'react';
import Field from "../components/forms/Field";
import {Link} from "react-router-dom";
import { USERS_API } from "../config";
import { toast } from "react-toastify";
import axios from 'axios';

const RegisterPage = ({ history }) => {
    const [user, setUser] = useState({
        firstname: "",
        lastname: "",
        email: "",
        address: "",
        postalCode: "",
        city: "",
        phone: "",
        numTVA:"",
        company:"",
        password: "",
        passwordConfirm: ""
    });

    const [errors, setErrors] = useState({
        firstname: "",
        lastname: "",
        email: "",
        address: "",
        postalCode: "",
        city: "",
        phone: "",
        numTVA:"",
        company:"",
        password: "",
        passwordConfirm: ""
    });

    //Gestion du changement dans les inputs du formulaire
    const handleChange = ({currentTarget}) => {
        const {value, name} = currentTarget;
        setUser({...user, [name]: value})
    };

    //Gestion de la soumission
    const handleSubmit = async event => {
        event.preventDefault();
        try {
            setErrors({});
            await axios.post(USERS_API, user);
            history.replace("/login");
            toast.success("Vous êtes désormais inscrit vous pouvez vous connecter");
        }  catch (error) {
            const { violations } = error.response.data;
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
            <h1>Inscription</h1>
            <form onSubmit={handleSubmit}>
                <Field
                    name="firstname"
                    type="text"
                    placeholder="Votre prénom"
                    label="Prénom"
                    onChange={handleChange}
                    value={user.firstname}
                    error={errors.firstname}
                />
                <Field
                    name="lastname"
                    type="text"
                    placeholder="Votre nom de famille"
                    label="Nom de famille"
                    onChange={handleChange}
                    value={user.lastname}
                    error={errors.lastname}
                />
                <Field
                    name="email"
                    type="email"
                    placeholder="Votre email"
                    label="Adresse email"
                    onChange={handleChange}
                    value={user.email}
                    error={errors.email}
                />
                <Field
                    name="address"
                    type="text"
                    placeholder="Entrez votre adresse"
                    label="Adresse"
                    onChange={handleChange}
                    value={user.address}
                    error={errors.address}
                />
                <Field
                    name="postalCode"
                    type="text"
                    placeholder="Entrez votre code postal"
                    label="Code postal"
                    onChange={handleChange}
                    value={user.postalCode}
                    error={errors.postalCode}
                />
                <Field
                    name="city"
                    type="text"
                    placeholder="Entrez votre ville"
                    label="Ville"
                    onChange={handleChange}
                    value={user.city}
                    error={errors.city}
                />
                <Field
                    name="phone"
                    type="text"
                    placeholder="Entrez votre numéro de téléphone"
                    label="Téléphone"
                    onChange={handleChange}
                    value={user.phone}
                    error={errors.phone}
                />
                <Field
                    name="numTVA"
                    type="number"
                    placeholder="Entrez votre numéro de TVA"
                    label="Numéro de TVA"
                    onChange={handleChange}
                    value={user.numTVA}
                    error={errors.numTVA}
                />
                <Field
                    name="company"
                    type="text"
                    placeholder="Entrez le nom de votre société"
                    label="Société"
                    onChange={handleChange}
                    value={user.company}
                    error={errors.company}
                />
                <Field
                    name="password"
                    type="password"
                    placeholder="Votre mot de passe"
                    label="Mot de passe"
                    onChange={handleChange}
                    value={user.password}
                    error={errors.password}
                />
                <Field
                    name="passwordConfirm"
                    type="password"
                    placeholder="Confirmez votre super mot de passe"
                    label="Confirmation du mot de passe"
                    onChange={handleChange}
                    value={user.passwordConfirm}
                    error={errors.passwordConfirm}
                />
                <div className="form-group">
                    <button type="submit" className="btn btn-success">Confirmation</button>
                    <Link to="/login" className="btn btn-link">J'ai déjà un compte</Link>
                </div>
            </form>
        </>
    );
};

export default RegisterPage;

