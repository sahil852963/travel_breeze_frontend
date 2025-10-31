import './App.css';
import { Routes, Route } from 'react-router-dom';
import {Home, SingleHotel} from './pages';

function App() {
  return (
    <Routes>
      <Route path="/" element={<Home />}></Route>
      <Route path="/hotel/:name/:address/:id/reserve" element={<SingleHotel />}></Route>
    </Routes>
  );
}

export default App;
