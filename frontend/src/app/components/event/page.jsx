'use client';

import { ShoppingCartOutlined } from '@ant-design/icons';
import { Button, Card, Typography } from 'antd';
import { useEffect, useState } from 'react';
import './OnlinePromotion.css';

const { Title } = Typography;

const OnlinePromotion = () => {
    const [events, setEvents] = useState([]);
    const [currentEvents, setCurrentEvents] = useState([]);
    const [upcomingEvents, setUpcomingEvents] = useState([]);
    const [selectedTab, setSelectedTab] = useState('current');
    const [countdown, setCountdown] = useState({ days: 0, hours: 0, minutes: 0, seconds: 0 });
    const [displayedBooks, setDisplayedBooks] = useState([]);

    useEffect(() => {
        const fetchEvents = async () => {
            try {
                const response = await fetch('http://localhost:8000/api/events');
                const data = await response.json();
                setEvents(data);
                categorizeEvents(data);
            } catch (error) {
                console.error('Error fetching events:', error);
            }
        };

        fetchEvents();
    }, []);

    const categorizeEvents = (eventsData) => {
        const now = new Date();
        const current = [];
        const upcoming = [];

        eventsData.forEach((event) => {
            const startDate = new Date(event.start_date);
            const endDate = new Date(event.end_date);

            if (startDate <= now && endDate >= now) {
                current.push(event);
            } else if (startDate > now) {
                upcoming.push(event);
            }
        });

        setCurrentEvents(current);
        setUpcomingEvents(upcoming);

        if (current.length > 0) {
            const allCurrentBooks = current.flatMap((event) => event.books);
            setDisplayedBooks(allCurrentBooks);
        }
    };

    useEffect(() => {
        let timer;
        const updateCountdown = (targetTime) => {
            const now = new Date().getTime();
            const distance = targetTime - now;

            if (distance > 0) {
                const days = Math.floor(distance / (1000 * 60 * 60 * 24));
                const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                const seconds = Math.floor((distance % (1000 * 60)) / 1000);
                setCountdown({ days, hours, minutes, seconds });
            } else {
                setCountdown({ days: 0, hours: 0, minutes: 0, seconds: 0 });
            }
        };

        if (selectedTab === 'current' && currentEvents.length > 0) {
            timer = setInterval(() => updateCountdown(new Date(currentEvents[0].end_date).getTime()), 1000);
        } else if (selectedTab === 'upcoming' && upcomingEvents.length > 0) {
            timer = setInterval(() => updateCountdown(new Date(upcomingEvents[0].start_date).getTime()), 1000);
        }

        return () => {
            if (timer) clearInterval(timer);
        };
    }, [selectedTab, currentEvents, upcomingEvents]);

    const handleTabChange = (tab) => {
        setSelectedTab(tab);
        if (tab === 'current' && currentEvents.length > 0) {
            const allCurrentBooks = currentEvents.flatMap((event) => event.books);
            setDisplayedBooks(allCurrentBooks);
        } else if (tab === 'upcoming' && upcomingEvents.length > 0) {
            const allUpcomingBooks = upcomingEvents.flatMap((event) => event.books);
            setDisplayedBooks(allUpcomingBooks);
        }
    };

    const calculateDiscountedPrice = (price, discount) => {
        const originalPrice = parseFloat(price);
        const discountAmount = originalPrice * (parseFloat(discount) / 100);
        return originalPrice - discountAmount;
    };

    const formatPrice = (price) => {
        return new Intl.NumberFormat('vi-VN').format(price) + 'đ';
    };

    const formatDateRange = (startDate, endDate) => {
        const start = new Date(startDate);
        const end = new Date(endDate);

        const startFormatted = `${start.getDate().toString().padStart(2, '0')}/${(start.getMonth() + 1)
            .toString()
            .padStart(2, '0')}`;
        const endFormatted = `${end.getDate().toString().padStart(2, '0')}/${(end.getMonth() + 1)
            .toString()
            .padStart(2, '0')}`;

        return `${startFormatted} - ${endFormatted}`;
    };

    return (
        <div className="bookstore-container">
            <div className="section">
                <div className="section-title">
                    <Title level={2}>Khuyến mãi Online</Title>
                </div>

                <div className="tab-navigation">
                    <div className="tabs">
                        <div
                            className={`tab ${selectedTab === 'current' ? 'active' : ''}`}
                            onClick={() => handleTabChange('current')}
                        >
                            <div className="tab-header2">
                                {currentEvents.length > 0
                                    ? formatDateRange(currentEvents[0].start_date, currentEvents[0].end_date)
                                    : ''}
                            </div>
                            <div className="tab-title">Đang diễn ra</div>
                        </div>

                        <div
                            className={`tab ${selectedTab === 'upcoming' ? 'active' : ''}`}
                            onClick={() => handleTabChange('upcoming')}
                        >
                            <div className="tab-header2">
                                {upcomingEvents.length > 0
                                    ? formatDateRange(upcomingEvents[0].start_date, upcomingEvents[0].end_date)
                                    : ''}
                            </div>
                            <div className="tab-title">Sắp diễn ra</div>
                        </div>
                    </div>

                    {(selectedTab === 'current' || selectedTab === 'upcoming') && (
                        <div className="countdown-container">
                            <span className="countdown-label">Kết thúc sau</span>
                            <div className="countdown-timer">
                                <span className="countdown-item">{countdown.days.toString().padStart(2, '0')}</span>
                                <span className="countdown-item">{countdown.hours.toString().padStart(2, '0')}</span>
                                <span className="countdown-item">{countdown.minutes.toString().padStart(2, '0')}</span>
                                <span className="countdown-item">{countdown.seconds.toString().padStart(2, '0')}</span>
                            </div>
                        </div>
                    )}
                </div>

                <div className="books-grid">
                    {displayedBooks.map((book) => (
                        <div key={book.id} className="book-grid-item">
                            <Card className="book-card">
                                <div className="book-image-container">
                                    <img
                                        src={book?.thumb || 'https://via.placeholder.com/300x400?text=No+Image'}
                                        alt={book.title}
                                        className="book-image"
                                        onError={(e) => {
                                            e.target.src = 'https://via.placeholder.com/300x400?text=No+Image';
                                        }}
                                    />
                                    <div className="discount-badge">ƯU ĐÃI ĐẾN 50%!!!</div>
                                    <div className="book-actions">
                                        <Button type="text" icon={<ShoppingCartOutlined />} className="cart-btn" />
                                    </div>
                                </div>

                                <div className="book-info">
                                    <h3 className="book-title">{book.title}</h3>
                                    <span className="book-author">Số lượng: {book.quantity_limit}</span>
                                    <span className="book-author">Đã bán: {book.sold_quantity}</span>

                                    <div className="price-container">
                                        <span className="current-price">
                                            {formatPrice(calculateDiscountedPrice(book.price, book.discount_percent))}
                                        </span>
                                        <span className="original-price">{formatPrice(book.price)}</span>
                                        <span className="discount-price">-{book.discount_percent}%</span>
                                    </div>
                                </div>
                            </Card>
                        </div>
                    ))}
                </div>
            </div>
        </div>
    );
};

export default OnlinePromotion;
