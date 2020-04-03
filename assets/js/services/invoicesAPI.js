import axios from "axios";

function findAll() {
    return axios
        .get("http://127.0.0.1:8000/api/invoices")
        .then(response => response.data['hydra:member']);
}

function deleteInvoice(id) {
    return axios
        .delete("http://127.0.0.1:8000/api/invoices/" + id)
}

function findInvoice(id) {
    return axios
        .get("http://127.0.0.1:8000/api/invoices/" + id)
        .then(response => response.data);
}

function createInvoice(invoice) {
    return  axios
        .post("http://localhost:8000/api/invoices",
        {...invoice, customer: `/api/customers/${invoice.customer}`});
}

function editInvoice(id, invoice) {
    axios.put("http://127.0.0.1:8000/api/invoices/" + id, {...invoice, customer: `/api/customers/${invoice.customer}`});
}

export default {
    findAll,
    delete: deleteInvoice,
    findInvoice,
    createInvoice,
    editInvoice
}