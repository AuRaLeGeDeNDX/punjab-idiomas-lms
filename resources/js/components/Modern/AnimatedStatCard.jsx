import React, { useState, useEffect } from 'react';
import { motion } from 'framer-motion';

/**
 * Animated Stat Card with Count-Up Effect
 * BOLD, GRADIENT, 3D HOVER
 */
const AnimatedStatCard = ({ 
    icon, 
    number, 
    label, 
    subtitle, 
    variant = 'primary',
    delay = 0 
}) => {
    const [count, setCount] = useState(0);

    // Count-up animation
    useEffect(() => {
        let start = 0;
        const end = parseInt(number);
        const duration = 2000; // 2 seconds
        const increment = end / (duration / 16);

        const timer = setInterval(() => {
            start += increment;
            if (start >= end) {
                setCount(end);
                clearInterval(timer);
            } else {
                setCount(Math.floor(start));
            }
        }, 16);

        return () => clearInterval(timer);
    }, [number]);

    const variants = {
        primary: 'modern-stat-card',
        success: 'modern-stat-card variant-success',
        warning: 'modern-stat-card variant-warning',
        info: 'modern-stat-card variant-info'
    };

    return (
        <motion.div
            className={variants[variant]}
            initial={{ opacity: 0, y: 50, scale: 0.9 }}
            animate={{ opacity: 1, y: 0, scale: 1 }}
            transition={{ 
                duration: 0.6, 
                delay: delay,
                type: "spring",
                stiffness: 100
            }}
            whileHover={{ 
                y: -12, 
                scale: 1.02,
                transition: { duration: 0.3 }
            }}
        >
            <motion.div 
                className="icon"
                animate={{ 
                    y: [0, -10, 0],
                }}
                transition={{
                    duration: 3,
                    repeat: Infinity,
                    ease: "easeInOut"
                }}
            >
                <i className={icon}></i>
            </motion.div>
            
            <motion.div 
                className="number"
                initial={{ scale: 0 }}
                animate={{ scale: 1 }}
                transition={{ 
                    delay: delay + 0.3,
                    type: "spring",
                    stiffness: 200
                }}
            >
                {count.toLocaleString()}
            </motion.div>
            
            <div className="label">{label}</div>
            {subtitle && <div className="subtitle">{subtitle}</div>}
        </motion.div>
    );
};

export default AnimatedStatCard;
