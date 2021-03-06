import React, {useState, useContext} from 'react';
import AuthAPI from '../services/authAPI';
import AuthContext from "../contexts/AuthContext";
import Field from "../components/forms/Field";
import { toast } from "react-toastify";
import {Link} from "react-router-dom";

const LoginPage = ({onLogin, history}) => {
    const {setIsAuthenticated} = useContext(AuthContext);

    const [credentials, setCredentials] = useState({
        username: "",
        password: ""
    });

    const [error, setError] = useState("");

    //Gestion des inputs
    const handleChange = ({currentTarget}) => {
        const {value, name} = currentTarget;
        setCredentials({...credentials, [name]: value})
    };

    //Gestion du submit
    const handleSubmit = async event => {
        event.preventDefault();
        try {
            await AuthAPI.authenticate(credentials);
            setError("");
            setIsAuthenticated(true);
            toast.success("Vous êtes désormais connecté !");
            history.replace("/customers");
        } catch (error) {
            setError("Aucun compte ne possède cette adresse ou alors les informations ne correspondent pas !");
            toast.error("Oups! Les informations fournies semblent erronées !");
        }
    };

    return (
        <>
            <div className="w-75 mx-auto">
                <h1>Connexion à l'application</h1>
                <form onSubmit={handleSubmit}>
                    <Field label="Adresse email"
                           name="username"
                           value={credentials.username}
                           onChange={handleChange}
                           type="email"
                           placeholder="Adresse email de connexion"
                           error={error}
                    />
                    <Field label="Mot de passe"
                           name="password"
                           value={credentials.password}
                           onChange={handleChange}
                           type="password"
                           id="password"
                           error=""
                    />
                    <div className="form-group d-flex justify-content-end">
                        <Link to="/reset/password" className="btn btn-link">Mot de passe oublié ?</Link>
                        <button type="submit" className="btn btn-outline-success">
                            Je me connecte
                        </button>
                    </div>
                </form>
            </div>
        </>
    );
};

export default LoginPage;