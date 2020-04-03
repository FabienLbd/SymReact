import axios from "axios";

function findAll() {
    return axios
        .get("http://127.0.0.1:8000/api/customers")
        .then(response => response.data['hydra:member']);
}

function deleteCustomer(id) {
    return axios
        .delete("http://127.0.0.1:8000/api/customers/" + id)
}

function createCustomer(customer) {
    return axios
        .post("http://127.0.0.1:8000/api/customers", customer);
}

function editCustomer(id, customer) {
    return axios
        .put("http://127.0.0.1:8000/api/customers/" + id, customer);
}

function findCustomer(id) {
    return axios
        .get("http://127.0.0.1:8000/api/customers/" + id)
        .then(response => response.data);
}

export default {
    findAll,
    delete: deleteCustomer,
    createCustomer,
    findCustomer,
    editCustomer
}