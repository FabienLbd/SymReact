import axios from 'axios';
import {USERS_API} from "../config";

function registerUser(user) {
    axios.post(USERS_API, user);
}

function findUserById(id) {
    axios.get(USERS_API + "/" + id)
         .then(response => response.data);
}

function editUser(id, user) {
    return axios
        .put(USERS_API + "/" + id, user);
}

export default {
    register: registerUser,
    findUser: findUserById,
    editUser
}