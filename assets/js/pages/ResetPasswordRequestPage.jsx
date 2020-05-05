import React, {useState} from 'react';
import Field from "../components/forms/Field";
import { toast } from "react-toastify";
import axios from "axios";
import {USERS_API} from "../config";

const ResetPasswordRequestPage = ({ history}) => {
    const [user, setUser] = useState({
        email: ""
    });

    const [errors, setErrors] = useState({
        email: ""
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
            await axios.post(USERS_API + "/resetPasswordRequest", user)
            toast.success("C'est bon! Un email de réinitialisation du mot de passe vous a été envoyé!");
            history.replace("/");
        } catch ({response}) {
            const { violations } = response.data;
            if (violations) {
                const apiErrors = {};
                violations.map(({propertyPath, message}) => {
                    apiErrors[propertyPath] = message;
                });
                setErrors(apiErrors);
            }
            toast.error("Aucun compte ne possède cette adresse email !");
        }
    };

    return (
        <>
            <div className="w-75 mx-auto">
                <h1>Mot de passe oublié</h1>
                <form onSubmit={handleSubmit}>
                    <Field
                        isRequired={true}
                        label="Email"
                        name="email"
                        value={user.email}
                        onChange={handleChange}
                        type="email"
                        placeholder="Entrer votre adresse email"
                        error={errors.email}
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

export default ResetPasswordRequestPage;