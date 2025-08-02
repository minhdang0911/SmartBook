'use client';
import React, { useState, useEffect, useRef } from 'react';
import { Card, Spin } from 'antd';

const AdvancedGSAPPublishersMarquee = () => {
  const [publishers, setPublishers] = useState([]);
  const [loading, setLoading] = useState(true);
  const marqueeRef = useRef(null);
  const containerRef = useRef(null);

  // Fetch publishers from API
  useEffect(() => {
    const fetchPublishers = async () => {
      try {
        const response = await fetch('http://localhost:8000/api/publisher');
        const data = await response.json();
        if (data.status) {
          setPublishers(data.data);
        }
      } catch (error) {
        console.error('Error fetching publishers:', error);
        setPublishers([
          { id: 19, name: "NXB Dân Trí", image_url: "https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQoBtRquhZ2CHH_QpxL7tpDFD8QaF7sSdm9dA&s" },
          { id: 17, name: "NXB Hà Nội", image_url: "https://play-lh.googleusercontent.com/J1iTXkL4lWni2x2iyhMJB-THqZnZyuwJyDB52H5DYo09s1AD7yIIFZikv9iiCFl0pg" },
          { id: 22, name: "NXB Hội Nhà Văn", image_url: "https://www.netabooks.vn/data/author/18246/logo--nxb-hoi-nha-van.jpg" },
          { id: 20, name: "NXB Lao Động", image_url: "https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQuvopYf5opN1k1TaIyBPhmxIHrZo3hhQ00yA&s" },
          { id: 21, name: "NXB Thế Giới", image_url: "https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTSdDuCUYMiVxHUd-NhA0gnWoJkXv8MNq6eDw&s" },
          { id: 18, name: "NXB Văn Học", image_url: "https://bizweb.dktcdn.net/thumb/grande/100/370/339/articles/62546969-logo-nxb-van-hoc-1a3f50ce-15aa-4748-8c11-b7b494553f51.jpg?v=1576158807580" }
        ]);
      } finally {
        setLoading(false);
      }
    };

    fetchPublishers();
  }, []);

  // Advanced GSAP-style animations
  useEffect(() => {
    if (!loading && publishers.length > 0 && marqueeRef.current) {
      const marqueeElement = marqueeRef.current;
      const cards = marqueeElement.querySelectorAll('.publisher-card');
      
      // Stagger animation for initial load
      cards.forEach((card, index) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(50px) scale(0.8)';
        
        setTimeout(() => {
          card.style.transition = 'all 0.8s cubic-bezier(0.34, 1.56, 0.64, 1)';
          card.style.opacity = '1';
          card.style.transform = 'translateY(0) scale(1)';
        }, index * 100);
      });

      // Marquee animation
      const startMarquee = () => {
        marqueeElement.style.animation = 'none';
        marqueeElement.offsetHeight;
        marqueeElement.style.animation = 'advancedMarquee 30s linear infinite';
      };

      setTimeout(() => {
        startMarquee();
      }, 2000);

      // Advanced hover interactions
      const handleMouseEnter = () => {
        marqueeElement.style.animationPlayState = 'paused';
        marqueeElement.style.filter = 'blur(0px)';
      };

      const handleMouseLeave = () => {
        marqueeElement.style.animationPlayState = 'running';
      };

      marqueeElement.addEventListener('mouseenter', handleMouseEnter);
      marqueeElement.addEventListener('mouseleave', handleMouseLeave);

      return () => {
        marqueeElement.removeEventListener('mouseenter', handleMouseEnter);
        marqueeElement.removeEventListener('mouseleave', handleMouseLeave);
      };
    }
  }, [loading, publishers]);

  if (loading) {
    return (
      <div style={{
        display: 'flex',
        justifyContent: 'center',
        alignItems: 'center',
        height: '400px',
        background: 'linear-gradient(135deg, #1e3c72 0%, #2a5298 50%, #667eea 100%)'
      }}>
        <div style={{ textAlign: 'center' }}>
          <Spin size="large" />
          <p style={{ color: 'white', marginTop: '20px', fontSize: '16px' }}>Đang tải nhà xuất bản...</p>
        </div>
      </div>
    );
  }

  const triplePublishers = [...publishers, ...publishers, ...publishers];

  return (
    <>
      <style jsx>{`
        .marquee-container {
          background: linear-gradient(135deg, #1e3c72 0%, #2a5298 50%, #667eea 100%);
          position: relative;
          overflow: hidden;
          padding: 80px 0;
          min-height: 500px;
        }

        .marquee-container::before {
          content: '';
          position: absolute;
          top: 0;
          left: 0;
          right: 0;
          bottom: 0;
          background: 
            radial-gradient(circle at 25% 25%, rgba(255, 255, 255, 0.1) 0%, transparent 50%),
            radial-gradient(circle at 75% 75%, rgba(255, 255, 255, 0.05) 0%, transparent 50%);
        }

        .animated-bg {
          position: absolute;
          width: 100%;
          height: 100%;
          overflow: hidden;
          pointer-events: none;
        }

        .bg-orb {
          position: absolute;
          border-radius: 50%;
          background: linear-gradient(45deg, rgba(255, 255, 255, 0.1), rgba(255, 255, 255, 0.05));
          animation: orbFloat 20s infinite ease-in-out;
          backdrop-filter: blur(40px);
        }

        .bg-orb:nth-child(1) {
          width: 200px;
          height: 200px;
          top: 10%;
          left: 80%;
          animation-delay: 0s;
        }

        .bg-orb:nth-child(2) {
          width: 150px;
          height: 150px;
          top: 70%;
          left: 10%;
          animation-delay: 7s;
        }

        .bg-orb:nth-child(3) {
          width: 100px;
          height: 100px;
          top: 40%;
          left: 70%;
          animation-delay: 14s;
        }

        .bg-orb:nth-child(4) {
          width: 120px;
          height: 120px;
          top: 20%;
          left: 30%;
          animation-delay: 3s;
        }

        @keyframes orbFloat {
          0%, 100% {
            transform: translate(0, 0) rotate(0deg) scale(1);
          }
          25% {
            transform: translate(30px, -30px) rotate(90deg) scale(1.1);
          }
          50% {
            transform: translate(-20px, 20px) rotate(180deg) scale(0.9);
          }
          75% {
            transform: translate(40px, 10px) rotate(270deg) scale(1.05);
          }
        }

        .header-section {
          text-align: center;
          margin-bottom: 60px;
          position: relative;
          z-index: 3;
        }

        .header-badge {
          display: inline-flex;
          align-items: center;
          padding: 12px 24px;
          background: rgba(255, 255, 255, 0.15);
          backdrop-filter: blur(20px);
          border: 1px solid rgba(255, 255, 255, 0.2);
          border-radius: 50px;
          color: white;
          font-size: 14px;
          font-weight: 500;
          margin-bottom: 20px;
          letter-spacing: 0.5px;
          animation: badgePulse 3s infinite ease-in-out;
        }

        @keyframes badgePulse {
          0%, 100% {
            transform: scale(1);
            box-shadow: 0 0 0 0 rgba(255, 255, 255, 0.3);
          }
          50% {
            transform: scale(1.05);
            box-shadow: 0 0 0 15px rgba(255, 255, 255, 0);
          }
        }

        .header-title {
          font-size: 48px;
          font-weight: 800;
          background: linear-gradient(135deg, #ffffff 0%, #f8fafc 50%, #e2e8f0 100%);
          -webkit-background-clip: text;
          -webkit-text-fill-color: transparent;
          background-clip: text;
          margin-bottom: 20px;
          text-shadow: 0 4px 8px rgba(0,0,0,0.3);
          letter-spacing: -1px;
          line-height: 1.2;
        }

        .header-subtitle {
          font-size: 20px;
          color: rgba(255, 255, 255, 0.85);
          max-width: 700px;
          margin: 0 auto;
          line-height: 1.7;
          font-weight: 300;
        }

        .marquee-track {
          position: relative;
          z-index: 2;
          overflow: hidden;
          padding: 30px 0;
        }

        .marquee-content {
          display: flex;
          gap: 40px;
          animation: advancedMarquee 30s linear infinite;
          width: max-content;
        }

        @keyframes advancedMarquee {
          0% {
            transform: translateX(0);
          }
          100% {
            transform: translateX(-33.333%);
          }
        }

        .publisher-card {
          flex-shrink: 0;
          width: 200px;
          height: 200px;
          background: rgba(255, 255, 255, 0.95);
          backdrop-filter: blur(20px);
          border-radius: 50%;
          display: flex;
          flex-direction: column;
          align-items: center;
          justify-content: center;
          text-align: center;
          transition: all 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
          border: 3px solid rgba(255, 255, 255, 0.3);
          box-shadow: 
            0 20px 60px rgba(0, 0, 0, 0.2),
            0 8px 30px rgba(0, 0, 0, 0.1),
            inset 0 1px 0 rgba(255, 255, 255, 0.8);
          cursor: pointer;
          position: relative;
          overflow: hidden;
          padding: 30px;
        }

        .publisher-card::before {
          content: '';
          position: absolute;
          top: 0;
          left: 0;
          right: 0;
          bottom: 0;
          background: conic-gradient(
            from 0deg,
            transparent 0deg,
            rgba(102, 126, 234, 0.3) 90deg,
            rgba(118, 75, 162, 0.3) 180deg,
            transparent 270deg,
            transparent 360deg
          );
          border-radius: 50%;
          opacity: 0;
          transition: all 0.6s ease;
          animation: rotate 10s linear infinite;
        }

        @keyframes rotate {
          from {
            transform: rotate(0deg);
          }
          to {
            transform: rotate(360deg);
          }
        }

        .publisher-card:hover::before {
          opacity: 1;
        }

        .publisher-card::after {
          content: '';
          position: absolute;
          top: 50%;
          left: 50%;
          width: 0;
          height: 0;
          background: radial-gradient(circle, rgba(255, 255, 255, 0.3) 0%, transparent 70%);
          border-radius: 50%;
          transform: translate(-50%, -50%);
          transition: all 0.6s ease;
          pointer-events: none;
        }

        .publisher-card:hover::after {
          width: 300px;
          height: 300px;
        }

        .publisher-card:hover {
          transform: translateY(-20px) scale(1.15) rotateY(15deg);
          box-shadow: 
            0 40px 100px rgba(0, 0, 0, 0.4),
            0 15px 50px rgba(102, 126, 234, 0.3);
          border-color: rgba(255, 255, 255, 0.6);
        }

        .publisher-logo {
          width: 80px;
          height: 80px;
          border-radius: 50%;
          object-fit: cover;
          box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
          transition: all 0.4s ease;
          border: 3px solid rgba(255, 255, 255, 0.9);
          margin-bottom: 15px;
          position: relative;
          z-index: 2;
        }

        .publisher-card:hover .publisher-logo {
          transform: scale(1.2) rotateZ(10deg);
          box-shadow: 0 15px 40px rgba(0, 0, 0, 0.3);
          border-color: #667eea;
        }

        .publisher-info {
          position: relative;
          z-index: 2;
        }

        .publisher-name {
          font-size: 14px;
          font-weight: 700;
          color: #2c3e50;
          margin: 0;
          line-height: 1.3;
          transition: all 0.3s ease;
          letter-spacing: 0.3px;
        }

        .publisher-card:hover .publisher-name {
          color: #667eea;
          transform: translateY(-2px);
        }

        .publisher-subtitle {
          font-size: 11px;
          color: #64748b;
          margin-top: 8px;
          font-weight: 500;
          opacity: 0.8;
          transition: all 0.3s ease;
        }

        .publisher-card:hover .publisher-subtitle {
          color: #475569;
          opacity: 1;
        }

        .floating-particles {
          position: absolute;
          width: 100%;
          height: 100%;
          pointer-events: none;
          overflow: hidden;
        }

        .particle {
          position: absolute;
          width: 4px;
          height: 4px;
          background: rgba(255, 255, 255, 0.6);
          border-radius: 50%;
          animation: particleFloat 15s infinite linear;
        }

        .particle:nth-child(1) { left: 10%; animation-delay: 0s; }
        .particle:nth-child(2) { left: 20%; animation-delay: 2s; }
        .particle:nth-child(3) { left: 30%; animation-delay: 4s; }
        .particle:nth-child(4) { left: 40%; animation-delay: 6s; }
        .particle:nth-child(5) { left: 50%; animation-delay: 8s; }
        .particle:nth-child(6) { left: 60%; animation-delay: 10s; }
        .particle:nth-child(7) { left: 70%; animation-delay: 12s; }
        .particle:nth-child(8) { left: 80%; animation-delay: 14s; }
        .particle:nth-child(9) { left: 90%; animation-delay: 16s; }

        @keyframes particleFloat {
          0% {
            transform: translateY(100vh) rotate(0deg);
            opacity: 0;
          }
          10% {
            opacity: 1;
          }
          90% {
            opacity: 1;
          }
          100% {
            transform: translateY(-100px) rotate(360deg);
            opacity: 0;
          }
        }

        @media (max-width: 768px) {
          .header-title {
            font-size: 36px;
          }
          
          .header-subtitle {
            font-size: 18px;
            padding: 0 20px;
          }
          
          .publisher-card {
            width: 160px;
            height: 160px;
            padding: 20px;
          }
          
          .publisher-logo {
            width: 60px;
            height: 60px;
          }
          
          .publisher-name {
            font-size: 12px;
          }
        }
      `}</style>

      <div className="marquee-container" ref={containerRef}>
        {/* Animated Background */}
        <div className="animated-bg">
          <div className="bg-orb"></div>
          <div className="bg-orb"></div>
          <div className="bg-orb"></div>
          <div className="bg-orb"></div>
        </div>

        {/* Floating Particles */}
        <div className="floating-particles">
          {[...Array(9)].map((_, i) => (
            <div key={i} className="particle"></div>
          ))}
        </div>

        {/* Header Section */}
        <div className="header-section">
          <div className="header-badge">
            ✨ Đối Tác Uy Tín
          </div>
          <h2 className="header-title">Nhà Xuất Bản Hàng Đầu</h2>
          <p className="header-subtitle">
            Kết nối với những nhà xuất bản uy tín nhất Việt Nam, 
            mang đến nguồn tri thức phong phú và chất lượng cao
          </p>
        </div>

        {/* Advanced Marquee */}
        <div className="marquee-track">
          <div 
            className="marquee-content" 
            ref={marqueeRef}
          >
            {triplePublishers.map((publisher, index) => (
              <Card
                key={`${publisher.id}-${index}`}
                className="publisher-card"
                hoverable
                bordered={false}
              >
                <img
                  src={publisher.image_url}
                  alt={publisher.name}
                  className="publisher-logo"
                  onError={(e) => {
                    e.target.src = 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iODAiIGhlaWdodD0iODAiIHZpZXdCb3g9IjAgMCA4MCA4MCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPGNpcmNsZSBjeD0iNDAiIGN5PSI0MCIgcj0iNDAiIGZpbGw9IiNmMGY0ZjgiLz4KPHN2ZyB3aWR0aD0iNDAiIGhlaWdodD0iNDAiIHZpZXdCb3g9IjAgMCAyNCAyNCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB4PSIyMCIgeT0iMjAiPgo8cGF0aCBkPSJNMTIgMkM2LjQ4IDIgMiA2LjQ4IDIgMTJzNC40OCAxMCAxMCAxMCAxMC00LjQ4IDEwLTEwUzE3LjUyIDIgMTIgMnptMCAxOGMtNC40MSAwLTgtMy41OS04LThzMy41OS04IDgtOCA4IDMuNTkgOCA4LTMuNTkgOC04IDh6bTMuNS02TDEyIDEwbC0zLjUgNGgzVjE2aDFWMTRoM3oiIGZpbGw9IiM5Y2ExYTciLz4KPC9zdmc+Cjwvc3ZnPgo=';
                  }}
                />
                <div className="publisher-info">
                  <h3 className="publisher-name">{publisher.name}</h3>
                  <p className="publisher-subtitle">Nhà Xuất Bản</p>
                </div>
              </Card>
            ))}
          </div>
        </div>
      </div>
    </>
  );
};

export default AdvancedGSAPPublishersMarquee;