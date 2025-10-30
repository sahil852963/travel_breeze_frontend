import "./HotelCard.css";

export const HotelCard = ({ hotel }) => {
  const { _id, name, image, rating, price, address, city, state } = hotel;
  // console.log(image)
  return (
    <div className="relative hotelcard-container shadow cursor-pointer">
      <div>
        <img className="img" src={image} alt={name} />
        <div className="hotelcard-details">
          <div className="d-flex align-center">
            <span className="location">
              {address}, {state}
            </span>
            <span className="rating d-flex align-center">
              <span class="material-icons-outlined">star</span>
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
      <div></div>
    </div>
  );
};

// Just checked linked in medi cloud and since 2 weeks nothing has been posted anymore … I know linked in updated api, please check


// I’ve checked the issue and found that due to recent updates in the LinkedIn API, the connection between the company page and the plugin is no longer active. There’s also a warning in the admin panel stating that the plugin will stop working after 2nd November.

// We need to re-authenticate the plugin to restore the connection so it can start posting again. Although the message mentions 2nd November, LinkedIn has already restricted the old tokens’ permissions for posting on company pages, which is why it stopped working earlier.

