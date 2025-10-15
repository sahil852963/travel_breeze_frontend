import { Fragment, useState, useEffect } from "react";
import axios from "axios";
import "./Home.css";
import InfiniteScroll from "react-infinite-scroll-component";

import { Navbar, HotelCard } from "../../components";

export const Home = () => {
  const [hotels, setHotels] = useState([]);
  const [hasMore, setHasMore] = useState(true);
  const [currentIndex, setCurrentIndex] = useState(16);
  const [testData, setTestData] = useState();

  useEffect(() => {
    (async () => {
      try {
        const { data } = await axios.get(
          "https://travel-breeze.onrender.com/api/hotels"
        );
        setTestData(data);
        setHotels(data ? data.slice(0, 16) : []);
        // console.log(data);
      } catch (err) {
        console.log(err);
      }
    })();
  }, []);

  const fetchMoreData = () => {
    if (hotels.length >= testData.length) {
      setHasMore(false);
      return;
    }
    setTimeout(() => {
      if (hotels && hotels.length > 0) {
        setHotels(
          hotels.concat(testData.slice(currentIndex, currentIndex + 16))
        );
        setCurrentIndex((prev) => prev + 16);
      } else {
        setHotels([]);
      }
    }, 1000);
  };

  return (
    <Fragment>
      <Navbar />
      {hotels && hotels.length > 0 ? (
        <InfiniteScroll
          dataLength={hotels.length}
          next={fetchMoreData}
          hasMore={hasMore}
          loader={
            hotels.length > 0 && <h3 className="alert-text">Loading...</h3>
          }
          endMessage={<p className="alert-text">You have seen it all</p>}
        >
          <main className="main d-flex align-center wrap gap-larger">
            {
              hotels &&
                hotels.map((hotel) => (
                  <HotelCard key={hotel._id} hotel={hotel} />
                ))
            }
          </main>
        </InfiniteScroll>
      ) : (
        <></>
      )}
    </Fragment>
  );
};
