import axios from "axios";
import { useParams } from "react-router-dom";
import { Fragment, useEffect , useState } from "react";
import { HotelImages, Navbar, HotelDetail, FinalPrice } from '../../components';
import './SingleHotel.css';

export const SingleHotel = () => {

    const [singleHotel, setSingleHotel] = useState();
    const { id } = useParams();

    useEffect(() => {
        (async () => {
            try {
                const {data} = await axios.get(`https://travel-breeze.onrender.com/api/hotels/${id}`);
                setSingleHotel(data);
            } catch(err){
                console.log(err)
            }
        })()
    }, [id]);

    return (
        <Fragment>
            <Navbar />
            <main className="single-hotel-page">
                <p className="hotel-name-add">
                {singleHotel?.name ?? ''}, {singleHotel?.state ?? ''}
                </p>
                <HotelImages singleHotel={singleHotel} />
                <div className="d-flex">
                    <HotelDetail singleHotel={singleHotel} />
                    <FinalPrice singleHotel={singleHotel} />
                </div>
            </main>
        </Fragment>
    )
}