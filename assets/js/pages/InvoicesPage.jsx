import React, {useEffect, useState} from "react";
import Pagination from "../components/Pagination";
import InvoicesAPI from "../services/invoicesAPI";
import moment from "moment";
import {Link} from "react-router-dom";
import { toast } from "react-toastify";
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import {faTrashAlt, faPencilAlt, faEye, faPlusCircle} from '@fortawesome/fontawesome-free-solid';
import SpinnerLoader from "../components/loaders/SpinnerLoader";

const STATUS_CLASSES = {
    PAID: "success",
    SENT: "primary",
    CANCELLED: "danger"
};

const STATUS_LABELS = {
    PAID: "Payée",
    SENT: "Envoyée",
    CANCELLED: "Annulée"
};

const InvoicesPage = (props) => {
    const [invoices, setInvoices] = useState([]);
    const [currentPage, setCurrentPage] = useState(1);
    const [search, setsearch] = useState("");
    const [loading, setLoading] = useState(true);
    const itemsPerPage = 20;

    //Permet d'aller récupérer les factures
    const fetchInvoices = async () => {
        try {
            const data = await InvoicesAPI.findAll();
            setInvoices(data);
            setLoading(false);
        } catch (error) {
            toast.error("Oups! Il y a eu un problème lors du chargement des factures !");
        }
    };

    //Au chargement du composant, on va chercher les factures
    useEffect(() => {
        fetchInvoices()
    }, []);

    //Gestion du format de dates
    const formatDate = (str) => moment(str).format('DD/MM/YYYY');

    //supprimer une facture
    const handleDelete = async (id) => {
        const originalInvoices = [...invoices];
        setInvoices(invoices.filter(invoice => invoice.id !== id));

        try {
            await InvoicesAPI.delete(id)
            toast.success("Ok! La facture à bien été supprimée !");
        } catch (error) {
            toast.error("Oups! Une erreur est survenue !");
            setInvoices(originalInvoices);
        }
    };

    //Gestion du changement de page
    const handlePageChange = page => setCurrentPage(page);

    //gestion de la recherche
    const handleSearch = ({currentTarget}) => {
        setsearch(currentTarget.value);
        setCurrentPage(1);
    };

    //Filtrage des customers en fonction de la recherche
    const filteredInvoices = invoices.filter(
        i =>
            i.customer.firstname.toLowerCase().includes(search.toLowerCase()) ||
            i.customer.lastname.toLowerCase().includes(search.toLowerCase()) ||
            i.amount.toString().startsWith(search.toLowerCase()) ||
            STATUS_LABELS[i.status].toLowerCase().includes(search.toLowerCase())
    );

    //Pagination des données
    const paginatedInvoices = Pagination.getData(filteredInvoices, currentPage, itemsPerPage);

    return (
        <>
            <div className="mb-3 d-flex justify-content-between align-items-center">
                <h1 className="my-0">Liste des factures</h1>
                <Link className="btn btn-outline-primary" to="/invoices/new">
                    <FontAwesomeIcon icon={faPlusCircle} />
                    Créer une facture
                </Link>
            </div>

            <div className="form-group">
                <input type="text" onChange={handleSearch} value={search} className="form-control"
                       placeholder="Rechercher ..."/>
            </div>
            <table className="table table-hover table-responsive-md">
                <thead>
                <tr>
                    <th className="text-center text-warning">Numéro</th>
                    <th className="text-warning">Client</th>
                    <th className="text-warning">Date d'envoi</th>
                    <th className="text-center text-warning">Statut</th>
                    <th className="text-center text-warning">Montant</th>
                    <th className="text-center text-warning">Actions</th>
                    <th className="text-center text-warning">Détails</th>
                </tr>
                </thead>
                { !loading && ( <tbody>
                {paginatedInvoices.map(invoice => <tr key={invoice.id}>
                        <td className="text-center align-middle">{invoice.chrono}</td>
                        <td className="align-middle">
                            <Link to={"/customers/" + invoice.customer.id}>
                                {invoice.customer.firstname} {invoice.customer.lastname}
                            </Link>
                        </td>
                        <td className="align-middle">{formatDate(invoice.sentAt)}</td>
                        <td className="text-center align-middle">
                            <span className={"badge badge-pill badge-" + STATUS_CLASSES[invoice.status]}>{STATUS_LABELS[invoice.status]}</span>
                        </td>
                        <td className="text-center align-middle">{invoice.amount.toLocaleString()} €</td>
                        <td className="text-center">
                            <Link className="btn btn-sm btn-outline-primary text-center mr-md-1" to={"/invoices/" + invoice.id}>
                                <FontAwesomeIcon icon={faPencilAlt} />
                                Editer
                            </Link>
                            <button
                                onClick={() => handleDelete(invoice.id)}
                                className="btn btn-sm btn-outline-danger text-center"
                            >
                                <FontAwesomeIcon icon={faTrashAlt} />
                            </button>
                        </td>
                        <td className="text-center">
                            <Link className="btn btn-sm btn-outline-primary text-center" to={"/invoices/show/" + invoice.id}>
                                <FontAwesomeIcon icon={faEye} />
                                Détails
                            </Link>
                        </td>
                    </tr>
                )}

                </tbody>
                )}
            </table>
            {loading && <SpinnerLoader/>}
            {itemsPerPage < filteredInvoices.length && (
                <Pagination currentPage={currentPage}
                            itemsPerPage={itemsPerPage}
                            length={filteredInvoices.length}
                            onPageChanged={handlePageChange}/>
            )}
        </>
    );
};

export default InvoicesPage;