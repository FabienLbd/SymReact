import React, {useState} from 'react';
import Field from "../components/forms/Field";
import { toast } from "react-toastify";
import axios from "axios";
import {USERS_API} from "../config";
import AuthAPI from "../services/authAPI";

const ResetPasswordPage = ({history, match}) => {
    const { id } = match.params;

    const [user, setUser] = useState({
        oldPassword: "",
        password: "",
        passwordConfirm: ""
    });

    const [errors, setErrors] = useState({
        oldPassword: "",
        password: "",
        passwordConfirm: ""
    });

    //Gestion des inputs
    const handleChange = ({currentTarget}) => {
        const {value, name} = currentTarget;
        setUser({...user, [name]: value});
    };

    //Gestion de la soumission du formulaire
    const handleSubmit = async event => {
        event.preventDefault();
        try {
            setErrors({});
            await axios.put(USERS_API + "/" + id + "/resetPassword", user)
            toast.success("C'est bon! Votre mot de passe a bien été modifié, vous devez vous réauthentifier !");
            AuthAPI.logout();
            history.replace("/login");
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
                <h1>changement du mot de passe</h1>
                <form onSubmit={handleSubmit}>
                    <Field
                        isRequired={true}
                        label="Ancien mot de passe"
                        name="oldPassword"
                        value={user.oldPassword}
                        onChange={handleChange}
                        type="password"
                        placeholder="Votre ancien mot de passe"
                        error={errors.oldPassword}
                    />
                    <Field
                        isRequired={true}
                        name="password"
                        type="password"
                        placeholder="Votre mot de passe"
                        label="Nouveau mot de passe"
                        onChange={handleChange}
                        value={user.password}
                        error={errors.password}
                    />
                    <Field
                        isRequired={true}
                        name="passwordConfirm"
                        type="password"
                        placeholder="Confirmez votre super mot de passe"
                        label="Confirmation du mot de passe"
                        onChange={handleChange}
                        value={user.passwordConfirm}
                        error={errors.passwordConfirm}
                    />
                    <div className="form-group d-flex justify-content-end">
                        <button type="submit" className="btn btn-outline-success">
                            Confirmer
                        </button>
                    </div>
                </form>
            </div>
        </>
    );
};

export default ResetPasswordPage;