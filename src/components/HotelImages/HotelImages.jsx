import './HotelImages.css';

export const HotelImages = ({singleHotel}) => {
    if (!singleHotel) return;

    const {_id, image, imageArr} = singleHotel;
    // console.log(singleHotel);
    return (
        <div className="hotel-image-container d-flex gap-small">
            <div className="primary-image-container">
                <img className="primary-img" src={image} alt="hotel" />
            </div>
            <div className="d-flex wrap gap-small">
                {imageArr &&
                imageArr.map((image, index) => (
                    <img
                    key={index}
                    className="hotel-img"
                    src={image}
                    alt="hotel"
                    />
                ))}
            </div>
        </div>
    );
}