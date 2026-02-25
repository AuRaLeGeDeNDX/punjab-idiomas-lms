import React, { useState } from 'react';
import { motion, AnimatePresence } from 'framer-motion';

/**
 * Modern Navigation with Glassmorphism
 * FLOATING SIDEBAR, SMOOTH TRANSITIONS, ANIMATED INDICATORS
 */
const ModernNavigation = ({ items, activeItem, onItemClick }) => {
    const [hoveredItem, setHoveredItem] = useState(null);

    return (
        <motion.nav 
            className="modern-nav"
            initial={{ x: -100, opacity: 0 }}
            animate={{ x: 0, opacity: 1 }}
            transition={{ duration: 0.5, type: "spring" }}
        >
            {items.map((item, index) => (
                <motion.a
                    key={item.id}
                    href={item.href}
                    className={`modern-nav-item ${activeItem === item.id ? 'active' : ''}`}
                    onClick={(e) => {
                        e.preventDefault();
                        onItemClick(item.id);
                    }}
                    onMouseEnter={() => setHoveredItem(item.id)}
                    onMouseLeave={() => setHoveredItem(null)}
                    initial={{ x: -50, opacity: 0 }}
                    animate={{ x: 0, opacity: 1 }}
                    transition={{ delay: index * 0.1 }}
                    whileHover={{ x: 4 }}
                >
                    <motion.i 
                        className={item.icon}
                        animate={hoveredItem === item.id ? {
                            rotate: [0, -10, 10, -10, 0],
                            scale: [1, 1.1, 1]
                        } : {}}
                        transition={{ duration: 0.5 }}
                    />
                    <span>{item.label}</span>

                    {/* Active Indicator */}
                    <AnimatePresence>
                        {activeItem === item.id && (
                            <motion.div
                                style={{
                                    position: 'absolute',
                                    left: 0,
                                    top: '50%',
                                    width: '4px',
                                    height: '60%',
                                    background: 'white',
                                    borderRadius: '0 4px 4px 0'
                                }}
                                initial={{ scaleY: 0, y: '-50%' }}
                                animate={{ scaleY: 1, y: '-50%' }}
                                exit={{ scaleY: 0 }}
                                transition={{ duration: 0.3 }}
                            />
                        )}
                    </AnimatePresence>

                    {/* Hover Glow Effect */}
                    <AnimatePresence>
                        {hoveredItem === item.id && activeItem !== item.id && (
                            <motion.div
                                style={{
                                    position: 'absolute',
                                    inset: 0,
                                    background: 'rgba(102, 126, 234, 0.1)',
                                    borderRadius: '12px',
                                    zIndex: -1
                                }}
                                initial={{ opacity: 0 }}
                                animate={{ opacity: 1 }}
                                exit={{ opacity: 0 }}
                                transition={{ duration: 0.2 }}
                            />
                        )}
                    </AnimatePresence>
                </motion.a>
            ))}
        </motion.nav>
    );
};

export default ModernNavigation;
