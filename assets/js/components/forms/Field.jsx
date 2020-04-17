import React from 'react';

const Field = ({ name, label, value, onChange, placeholder = "", type = "text", error = "", isRequired = false }) =>
    (
        <div className="form-group">
            {
               isRequired ? <label htmlFor={name}>{label} <span className="text-danger">*</span></label> :
                   <label htmlFor={name}>{label}</label>
            }
            <input
                value={value}
                onChange={onChange}
                type={type}
                placeholder={placeholder || label}
                name={name} id={name}
                className={"form-control" + (error && " is-invalid")}
            />
            {error && <p className="invalid-feedback">{error}</p>}
        </div>
    );

export default Field;