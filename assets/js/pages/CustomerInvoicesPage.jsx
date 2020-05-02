import React, {useEffect, useState} from "react";
import Pagination from "../components/Pagination";
import InvoicesAPI from "../services/invoicesAPI";
import CustomerAPI from "../services/customersAPI";
import moment from "moment";
import {Link} from "react-router-dom";
import { toast } from "react-toastify";
import { FontAwesomeIcon } from '@fortawesome/react-fontawesome';
import {faTrashAlt, faPencilAlt, faEye, faPlusCircle} from '@fortawesome/fontawesome-free-solid';
import SpinnerLoader from "../components/loaders/SpinnerLoader";
import {faSortAmountDown, faSortAmountUpAlt} from "@fortawesome/free-solid-svg-icons";

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

const CustomerInvoicesPage = ({match}) => {
    const [invoices, setInvoices] = useState([]);
    const [currentCustomer, setCurrentCustomer] = useState({});
    const [currentPage, setCurrentPage] = useState(1);
    const [search, setSearch] = useState("");
    const [loading, setLoading] = useState(true);
    const [order, setOrder] = useState("desc");
    const [iconName, setIconName] = useState("");
    const itemsPerPage = 10;
    const {id} = match.params;

    //Permet d'aller récupérer les factures
    const fetchInvoices = async (id, order, sortProperty = "chrono") => {
        try {
            const data = await InvoicesAPI.findInvoicesByCustomer(id, order, sortProperty);
            setInvoices(data);
            setLoading(false);
        } catch (error) {
            toast.error("Oups! Il y a eu un problème lors du chargement des factures !");
        }
    };

    const fetchcustomer = async () => {
        try {
            const currentcustomer = await CustomerAPI.findCustomer(id);
            setCurrentCustomer(currentcustomer);
            setLoading(false);
        } catch (error) {
            toast.error("Oups! Il y a eu un problème lors du chargement des factures !");
        }
    }
    //Au chargement du composant, on va chercher le customer
    useEffect(() => {
        fetchcustomer(id);
    }, [id])

    //Au chargement du composant, on va chercher les factures
    useEffect(() => {
        fetchInvoices(id, order);
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
        setSearch(currentTarget.value);
        setCurrentPage(1);
    };

    //Filtrage des customers en fonction de la recherche
    const filteredInvoices = invoices.filter(
        i =>
            i.amount.toString().startsWith(search.toLowerCase()) ||
            STATUS_LABELS[i.status].toLowerCase().includes(search.toLowerCase())
    );

    //Pagination des données
    const paginatedInvoices = Pagination.getData(filteredInvoices, currentPage, itemsPerPage);

    const handleSort = ({currentTarget}) => {
        const targetId = currentTarget.id;
        if (order === "desc") {
            setOrder("asc");
        } else {
            setOrder("desc");
        }
        setIconName(`${targetId}-${order}`);
        fetchInvoices(id, order, targetId);
    }

    return (
        <>
            <div className="mb-3 d-flex justify-content-between align-items-center">
                <h1 className="my-0">Factures de {currentCustomer.firstname} {currentCustomer.lastname}</h1>
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
                    <th className="text-center text-warning">
                        Numéro
                        <button
                            id="chrono"
                            onClick={handleSort}
                            className="text-warning btn btn-sm btn-outline-light"
                        >
                            {iconName === "chrono-asc" ?
                                <FontAwesomeIcon icon={faSortAmountUpAlt} /> :
                                <FontAwesomeIcon icon={faSortAmountDown} />

                            }
                        </button>
                    </th>
                    <th className="text-warning">
                        Date d'envoi
                        <button
                            id="sentAt"
                            onClick={handleSort}
                            className="text-warning btn btn-sm btn-outline-light"
                        >
                            {iconName === "sentAt-asc" ?
                                <FontAwesomeIcon icon={faSortAmountUpAlt} /> :
                                <FontAwesomeIcon icon={faSortAmountDown} />

                            }
                        </button>
                    </th>
                    <th className="text-center text-warning">
                        Statut
                        <button
                            id="status"
                            onClick={handleSort}
                            className="text-warning btn btn-sm btn-outline-light"
                        >
                            {iconName === "status-asc" ?
                                <FontAwesomeIcon icon={faSortAmountUpAlt} /> :
                                <FontAwesomeIcon icon={faSortAmountDown} />

                            }
                        </button>
                    </th>
                    <th className="text-center text-warning">
                        Montant TTC
                        <button
                            id="amount"
                            onClick={handleSort}
                            className="text-warning btn btn-sm btn-outline-light"
                        >
                            {iconName === "amount-asc" ?
                                <FontAwesomeIcon icon={faSortAmountUpAlt} /> :
                                <FontAwesomeIcon icon={faSortAmountDown} />

                            }
                        </button>
                    </th>
                    <th className="text-center text-warning align-middle">Actions</th>
                    <th className="text-center text-warning align-middle">Détails</th>
                </tr>
                </thead>
                { !loading && ( <tbody>
                    {paginatedInvoices.map(invoice => <tr key={invoice.id}>
                            <td className="text-center align-middle">{invoice.chrono}</td>
                            <td className="align-middle">{formatDate(invoice.sentAt)}</td>
                            <td className="text-center align-middle">
                                <span className={"badge badge-pill badge-" + STATUS_CLASSES[invoice.status]}>{STATUS_LABELS[invoice.status]}</span>
                            </td>
                            <td className="text-center align-middle">{
                                ((invoice.amount + invoice.fee ) + (invoice.amount + invoice.fee) * 0.20).toLocaleString()} €</td>
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

export default CustomerInvoicesPage;