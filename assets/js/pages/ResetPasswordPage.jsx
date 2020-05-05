import React, {useState} from 'react';
import Field from "../components/forms/Field";
import { toast } from "react-toastify";
import axios from "axios";
import {USERS_API} from "../config";

const ResetPasswordPage = ({ history, match}) => {
    const {token} = match.params;

    const [user, setUser] = useState({
        password: "",
        passwordConfirm: ""
    });

    const [errors, setErrors] = useState({
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
            await axios.put(USERS_API + "/" + token + "/resetPassword", user)
            toast.success("C'est bon! votre mot de passe a été réinitialisé");
            history.replace("/login");
        } catch ({response}) {
            const { violations } = response.data;
            const message = response.data['hydra:description'];
            if (violations) {
                const apiErrors = {};
                violations.map(({propertyPath, message}) => {
                    apiErrors[propertyPath] = message;
                });
                setErrors(apiErrors);
            } else if(message)  {
                toast.error(message);
            } else {
                toast.error('Oups! Une erreur est survenue.');
            }
        }
    };

    return (
        <>
            <div className="w-75 mx-auto">
                <h1>Réinitialisation du mot de passe</h1>
                <form onSubmit={handleSubmit}>
                    <Field
                        isRequired={true}
                        label="Mot de passe"
                        name="password"
                        value={user.password}
                        onChange={handleChange}
                        type="password"
                        placeholder="Nouveau mot de passe"
                        error={errors.password}
                    />
                    <Field
                        isRequired={true}
                        label="Confirmer mot de passe"
                        name="passwordConfirm"
                        value={user.passwordConfirm}
                        onChange={handleChange}
                        type="password"
                        placeholder="Confimer nouveau mot de passe"
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