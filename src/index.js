import React from 'react';
import ReactDOM from 'react-dom/client';
import App from './App';
import { BrowserRouter as Router } from 'react-router-dom';
import { CategoryProvider, DateProvider } from './context';

const root = ReactDOM.createRoot(document.getElementById('root'));
root.render(
  <React.StrictMode>
    <Router>
      <CategoryProvider>
         <DateProvider>
          <App />
         </DateProvider>
      </CategoryProvider>
    </Router>
  </React.StrictMode>
);
