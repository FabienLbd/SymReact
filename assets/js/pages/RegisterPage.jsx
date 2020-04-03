import React, {useState} from 'react';
import Field from "../components/forms/Field";
import {Link} from "react-router-dom";
import usersAPI from "../services/usersAPI";

const RegisterPage = ({ history }) => {
    const [user, setUser] = useState({
        firstname: "",
        lastname: "",
        email: "",
        password: "",
        passwordConfirm: ""
    });

    const [errors, setErrors] = useState({
        firstname: "",
        lastname: "",
        email: "",
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
        const apiErrors = {};

        if (user.password !== user.passwordConfirm) {
            apiErrors.passwordConfirm = "Les deux mots de passes ne matchent pas !";
            setErrors(apiErrors);
            return;
        }
        try {
            await usersAPI.register(user);
            setErrors({});
            history.replace("/login");

            //TODO Flash message success

        }  catch (error) {
            console.log(error.response);
            const { violations } = error.response.data;
            if (violations) {

                violations.map(violation => {
                    apiErrors[violation.propertyPath] = violation.message;
                });
                setErrors(apiErrors);
                //TODO Flash message errors
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

