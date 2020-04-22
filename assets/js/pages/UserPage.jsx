import React, {useState, useEffect} from 'react';
import UsersAPI from "../services/usersAPI";
import { USERS_API } from "../config";
import {toast} from "react-toastify";
import axios from 'axios';
import {FontAwesomeIcon} from "@fortawesome/react-fontawesome";
import {faPencilAlt} from "@fortawesome/fontawesome-free-solid";
import {Link} from "react-router-dom";

const UserPage = ({ match, history }) => {
    const { id } = match.params;

    const [user, setUser] = useState({
        firstname: "",
        lastname: "",
        email: "",
        address: "",
        postalCode: "",
        city: "",
        numTVA: "",
        company: "",
        phone: ""
    });

    //Récuperation des données de l'utilisateur
    const fetchUser = async id => {
        try {
            const { firstname, lastname, email, address, postalCode, city, numTVA, company } =
                await axios.get(USERS_API + "/" + id)
                           .then(response => response.data);
            setUser({ firstname, lastname, email, address, postalCode, city, numTVA, company } );
        } catch (error) {
            toast.error("Une erreur est survenue ou la requête ne vous ai pas autorisée ");
            history.replace("/");
        }
    };

    //Chargement des données utilisateur si besoin au chargement du composant
    useEffect(() => {
        fetchUser(id);
    }, [id]);


    return (
        <>
            <div className="w-75 mx-auto">
                <div className="mb-3 d-flex justify-content-between align-items-center">
                    <h1 className="mb-3">Profile utilisateur</h1>
                    <Link className="btn btn-primary" to={"/user/edit/" + id}>
                        <FontAwesomeIcon icon={faPencilAlt} />
                        Editer le profile
                    </Link>
                </div>
                <table className="table table-borderless table-striped">
                    <tbody>
                    <tr>
                        <th scope="row">Nom</th>
                        <td>{user.firstname}</td>
                    </tr>
                    <tr>
                        <th scope="row">Prénom</th>
                        <td>{user.lastname}</td>
                    </tr>
                    <tr>
                        <th scope="row">Adresse email</th>
                        <td>{user.email}</td>
                    </tr>
                    <tr>
                        <th scope="row">Adresse postale</th>
                        <td>{user.address}</td>
                    </tr>
                    <tr>
                        <th scope="row">Code postale</th>
                        <td>{user.postalCode}</td>
                    </tr>
                    <tr>
                        <th scope="row">Ville</th>
                        <td>{user.city}</td>
                    </tr>
                    <tr>
                    <th scope="row">Numéro de TVA</th>
                        <td>{user.numTVA}</td>
                    </tr>
                    <tr>
                        <th scope="row">Nom de l'entreprise</th>
                        <td>{user.company}</td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </>
    );
}

export default UserPage;