import React, {useEffect, useState} from "react";
import Pagination from "../components/Pagination";
import CustomersAPI from "../services/customersAPI";
import {Link} from "react-router-dom";
import { toast } from "react-toastify";
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import {faTrashAlt, faPlusCircle, faChevronLeft, faEye} from '@fortawesome/fontawesome-free-solid';
import SpinnerLoader from "../components/loaders/SpinnerLoader";

const CustomersPage = (props) => {
    const [customers, setCustomers] = useState([]);
    const [currentPage, setCurrentPage] = useState(1);
    const [search, setsearch] = useState("");
    const [loading, setLoading] = useState(true);
    const [isDisplayingArchive, setIsDisplayingArchive] = useState(false);

    //Permet d'aller récupérer les customers
    const fetchCustomers = async () => {
        try {
            const data = await CustomersAPI.findAllActiveCustomers();
            setCustomers(data);
            setLoading(false);
        } catch (error) {
            toast.error("Oups! Il semblerai qu'il y ai un problème, veuillez réessayer");
        }
    };

    const fetchArchivedCustomers = async () => {
        try {
            const archivedData = await CustomersAPI.findAllArchivedCustomers();
            setCustomers(archivedData);
            setLoading(false);
        } catch(error) {
            toast.error("Oups! Il semblerai qu'il y ai un problème, veuillez réessayer");
        }
    }

    //Au chargement du composant, on va chercher les customers
    useEffect(() => {
        fetchCustomers();
    }, []);

    //Remove a customer
    const handleDelete = async (id) => {
        const originalCustomers = [...customers];
        setCustomers(customers.filter(customer => customer.id !== id));
        try {
            await CustomersAPI.delete(id);
            toast.success("Ok! Le client à bien été supprimé !");
        } catch (error) {
            setCustomers(originalCustomers);
            toast.error("Oups! Il semblerai qu'il y ai un problème !");
        }
    };

    //Gestion du changement de page
    const handlePageChange = page => setCurrentPage(page);

    //gestion de la recherche
    const handleSearch = ({currentTarget}) => {
        setsearch(currentTarget.value);
        setCurrentPage(1);
    };

    const switchToArchived = () => {
        if (isDisplayingArchive === false) {
            fetchArchivedCustomers();
            setIsDisplayingArchive(true);
        } else {
            fetchCustomers();
            setIsDisplayingArchive(false);
        }
    }

    const itemsPerPage = 10;

    //Filtrage des customers en fonction de la recherche
    const filteredCustomers = customers.filter(
        c =>
            c.firstname.toLowerCase().includes(search.toLowerCase()) ||
            c.lastname.toLowerCase().includes(search.toLowerCase()) ||
            c.email.toLowerCase().includes(search.toLowerCase()) ||
            (c.company && c.company.toLowerCase().includes(search.toLowerCase()))
    );

    //Pagination des données
    const paginatedCustomers = Pagination.getData(filteredCustomers, currentPage, itemsPerPage);

    return (
        <>
            <div className="mb-3 d-flex justify-content-between align-items-center">
                {!isDisplayingArchive && <h1>Liste des clients</h1> || <h1>Liste des clients archivés</h1>}
                <div>
                    <button className="btn btn-outline-primary mr-2" onClick={switchToArchived}>
                        { isDisplayingArchive &&
                            <>
                                <FontAwesomeIcon icon={faChevronLeft} />
                                Retour aux clients actifs
                            </> ||
                            <>
                                <FontAwesomeIcon icon={faEye} />
                                voir les clients archivés
                            </>

                        }
                    </button>
                    <Link className="btn btn-outline-primary" to="/customers/new">
                        <FontAwesomeIcon icon={faPlusCircle} />
                        Créer un client
                    </Link>
                </div>
            </div>

            <div className="form-group">
                <input type="text" onChange={handleSearch} value={search} className="form-control"
                       placeholder="Rechercher ..."/>
            </div>
            <table className="table table-hover table-responsive-md">
                <thead>
                <tr>
                    <th className="text-warning">Client</th>
                    <th className="text-warning">Email</th>
                    <th className="text-warning">Entreprise</th>
                    <th className="text-warning text-center">Factures</th>
                    <th className="text-warning text-center">Montant total HT</th>
                    <th className="text-warning text-center">Actions</th>
                </tr>
                </thead>
                { !loading && (
                    <tbody>
                    {paginatedCustomers.map(customer => <tr key={customer.id}>
                            <td className="align-middle">
                                <Link to={"/customers/" + customer.id}>
                                    {customer.firstname} {customer.lastname}
                                </Link>
                            </td>
                            <td className="align-middle">{customer.email}</td>
                            <td className="align-middle">{customer.company}</td>
                            <td className="text-center align-middle">
                                <span className="badge badge-pill badge-primary">{customer.invoices.length}</span>
                            </td>
                            <td className="text-center align-middle">{customer.totalAmount.toLocaleString()} €</td>
                            <td className="text-center">
                                <button
                                    onClick={() => handleDelete(customer.id)}
                                    disabled={customer.invoices.length > 0}
                                    className="btn btn-sm btn-outline-danger mr-1"
                                >
                                    <FontAwesomeIcon icon={faTrashAlt} />
                                    Supprimer
                                </button>
                                <Link className="btn btn-sm btn-outline-primary" to={"/customers/" + customer.id + "/invoices"}>
                                    <FontAwesomeIcon icon={faEye} />
                                    Factures
                                </Link>
                            </td>
                        </tr>
                    )}
                </tbody>
                )}
            </table>
            { loading && <SpinnerLoader/> }
            {itemsPerPage < filteredCustomers.length && (
                <Pagination currentPage={currentPage}
                            itemsPerPage={itemsPerPage}
                            length={filteredCustomers.length}
                            onPageChanged={handlePageChange}/>
            )}
        </>
    );
};

export default CustomersPage;
