import axios from "axios";
import { CUSTOMERS_API } from "../config";

function findAllActiveCustomers() {
    return axios
        .get(`${CUSTOMERS_API}?isArchived=false`)
        .then(response => response.data['hydra:member']);
}

function findAllArchivedCustomers() {
    return axios
        .get(`${CUSTOMERS_API}?isArchived=true`)
        .then(response => response.data['hydra:member']);
}

function deleteCustomer(id) {
    return axios
        .delete(CUSTOMERS_API + "/" + id)
}

function createCustomer(customer) {
    return axios
        .post(CUSTOMERS_API, customer);
}

function editCustomer(id, customer) {
    return axios
        .put(CUSTOMERS_API + "/" + id, customer);
}

function findCustomer(id) {
    return axios
        .get(CUSTOMERS_API + "/" + id)
        .then(response => response.data);
}

export default {
    findAllActiveCustomers,
    findAllArchivedCustomers,
    delete: deleteCustomer,
    createCustomer,
    findCustomer,
    editCustomer
}