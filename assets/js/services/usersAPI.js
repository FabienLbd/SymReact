import axios from 'axios';

function register(user) {
    axios.post("http://localhost:8000/api/users", user);
}

export default {
    register
}