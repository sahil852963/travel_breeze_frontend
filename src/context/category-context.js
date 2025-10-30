import { createContext, useContext, useState } from "react";


const initailValue = "National Parks";

const CategoryContext = createContext(initailValue);

const CategoryProvider = ({children}) => {

    const[hotelCategory, setHotelCategory] = useState(initailValue);

    return <CategoryContext.Provider value={{hotelCategory, setHotelCategory}}>
        {children}
    </CategoryContext.Provider>
}

const useCategory = () => useContext(CategoryContext);

export { useCategory, CategoryProvider}