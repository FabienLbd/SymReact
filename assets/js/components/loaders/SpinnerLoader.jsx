import React from 'react';
import Loader from 'react-loader-spinner'
const SpinnerLoader = (props) => {
    //other logic
        return(
            <>
                <div className="d-flex justify-content-center mt-5">
                    <Loader
                        type="ThreeDots"
                        color="#1A1A1A"
                        height={100}
                        width={100}
                        timeout={3000} //3 secs

                    />
                </div>

            </>
        );
}

export default SpinnerLoader;