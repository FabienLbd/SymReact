import axios from "axios";
import { INVOICES_API, CUSTOMERS_API } from "../config";

function findAll() {
    return axios
        .get(INVOICES_API)
        .then(response => response.data['hydra:member']);
}

function findInvoicesByCustomer(id, order, sortProperty) {
    return axios
        .get(CUSTOMERS_API + "/" + id + "/invoices?order[" + sortProperty + "]=" + order)
        .then(response => response.data['hydra:member']);
}

function deleteInvoice(id) {
    return axios
        .delete(INVOICES_API + "/" + id)
}

function findInvoice(id) {
    return axios
        .get(INVOICES_API + "/" + id)
        .then(response => response.data);
}

function createInvoice(invoice) {
    return  axios
        .post(INVOICES_API,
        {...invoice, customer: `/api/customers/${invoice.customer}`});
}

function editInvoice(id, invoice) {
    axios.put(INVOICES_API + "/" + id,
        {...invoice, customer: `/api/customers/${invoice.customer}`});
}

export default {
    findAll,
    findInvoicesByCustomer,
    delete: deleteInvoice,
    findInvoice,
    createInvoice,
    editInvoice
}