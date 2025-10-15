import "./HotelCard.css";

export const HotelCard = ({hotel}) => {
  
  const {_id, name, image, rating, price, address, city, state} = hotel;
  // console.log(image)
  return (
    <div className="relative hotelcard-container shadow cursor-pointer">
      <div>
        <img className="img" src={image} alt={name} />
        <div className="hotelcard-details">
          <div className="d-flex align-center">
            <span className="location">{address}, {state}</span>
            <span>
              <span>start</span>
              <span>{rating}</span>
            </span>
          </div>
          <p className="hotel-name">{name}</p>
          <p className="price-details">
            <span className="price">Rs. {price}</span>
            <span>night</span>
          </p>
        </div>
      </div>
        <button className="button btn-wishlist absolute d-flex align-center">
          <span className="material-icons favorite cursor">favorite</span>
        </button>
      <div>
      </div>
    </div>
  );
};
