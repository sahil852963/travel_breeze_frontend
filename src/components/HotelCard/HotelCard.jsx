import { useNavigate } from "react-router-dom";
import "./HotelCard.css";

export const HotelCard = ({ hotel }) => {
	const { _id, name, image, rating, price, address, city, state } = hotel;

	const navigate = useNavigate();

	const onSingleHotelCardClick = () => {
		navigate(`hotel/${name}/${address}-${state}/${_id}/reserve`);
	};

	// console.log(image)
	return (
		<div className="relative hotelcard-container shadow cursor-pointer">
			<div onClick={onSingleHotelCardClick}>
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
