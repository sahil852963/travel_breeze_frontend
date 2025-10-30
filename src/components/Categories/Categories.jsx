import { useEffect, useState } from "react";
import axios from "axios";
import './Categories.css';
import { useCategory } from "../../context";

export const Categories = () => {

    const [categories, setCategories] = useState([]);
    const [numberOfCateToShow, setNumberOfCateToShow] = useState(0);
    const {hotelCategory, setHotelCategory} = useCategory();

    const handleRightClick = () => {
        setNumberOfCateToShow(prev => prev + 10);
    }

    const handleLeftClick = () => {
        setNumberOfCateToShow(prev => prev - 10);
    }

    const handleCategoryClick = (category) => {
        setHotelCategory(category)
    }
    console.log("hotelCategory is - ", hotelCategory)

    useEffect(() => {
        (async () => {
            try {
                const { data } = await axios.get('https://travel-breeze.onrender.com/api/categories');

                const showCategories = data.slice(
                    numberOfCateToShow + 10 > data.length ? data.length - 10 : numberOfCateToShow, 
                    numberOfCateToShow > data.length ? data.length : numberOfCateToShow + 10
                )
                // console.log(showCategories);
                setCategories(showCategories)
            } catch (err) {
                console.log(err)
            }
        })()
    }, [numberOfCateToShow])
    
    return (
        <section className="categories d-flex align-center gap-large cursor-pointer">
            {
                numberOfCateToShow >= 10 && (
                <button onClick={handleLeftClick}>
                    <span className="material-icons-outlined">chevron_left</span>
                </button>
                )
            }
            {
                categories && categories.map(({_id, category}) => (<span className={`${category === hotelCategory ? "border-bottom" : ""}`} key={_id} onClick={() => handleCategoryClick(category)}>{category}</span>))
            }

            {
                numberOfCateToShow -10 < categories.length && (
                    <button onClick={handleRightClick}>
                        <span className="material-icons-outlined">chevron_right</span>
                    </button>
                )
            }
        </section>
    );
}