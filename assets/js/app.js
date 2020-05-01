import React, {useState, useContext} from 'react';
import ReactDOM from "react-dom";
import Navbar from "./components/Navbar";
import HomePage from "./pages/Homepage";
import {HashRouter, Switch, Route, withRouter, Redirect} from "react-router-dom";
import CustomersPage from "./pages/CustomersPage";
import InvoicesPage from "./pages/InvoicesPage";
import LoginPage from "./pages/LoginPage";
import AuthAPI from './services/authAPI';
import AuthContext from './contexts/AuthContext';
import PrivateRoute from "./components/PrivateRoute";
import CustomerPage from "./pages/CustomerPage";
import CustomerInvoicesPage from "./pages/CustomerInvoicesPage";
import Invoice from "./pages/Invoice";
import RegisterPage from "./pages/RegisterPage";
import {ToastContainer} from "react-toastify";
import { toast } from "react-toastify";
import InvoiceShow from "./pages/InvoiceShow";
import UserPage from "./pages/UserPage";
import UserEdit from "./pages/UserEdit";
import ResetPasswordPage from "./pages/ResetPasswordPage";

import '../css/app.css';
import 'react-toastify/dist/ReactToastify.css';

AuthAPI.setup();

const App = () => {
    const [isAuthenticated, setIsAuthenticated] = useState(
        AuthAPI.isAuthenticated()
    );
    const Navbarwithrouter = withRouter(Navbar);

    return (
        <AuthContext.Provider value={{
            isAuthenticated,
            setIsAuthenticated
        }}>
            <HashRouter>
                <Navbarwithrouter />
                <main className="container pt-5">
                    <Switch>
                        <Route path="/login" component={LoginPage} />
                        <Route path="/register" component={RegisterPage} />
                        <PrivateRoute path="/users/:id/resetPassword" component={ResetPasswordPage} />
                        <PrivateRoute path="/users/edit/:id" component={UserEdit} />
                        <PrivateRoute path="/users/:id" component={UserPage} />
                        <PrivateRoute path="/invoices/show/:id" component={InvoiceShow} />
                        <PrivateRoute path="/invoices/:id" component={Invoice} />
                        <PrivateRoute path="/invoices" component={InvoicesPage} />
                        <PrivateRoute path="/customers/:id/invoices" component={CustomerInvoicesPage} />
                        <PrivateRoute path="/customers/:id" component={CustomerPage} />
                        <PrivateRoute path="/customers" component={CustomersPage} />
                        <Route path="/" component={HomePage} />
                    </Switch>
                </main>
            </HashRouter>
            <ToastContainer position={toast.POSITION.TOP_LEFT}/>
        </AuthContext.Provider>
    );
};

const rootElement = document.querySelector('#app');
ReactDOM.render(<App/>, rootElement);