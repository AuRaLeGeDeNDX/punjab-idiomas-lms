import React from 'react';
import { motion } from 'framer-motion';

/**
 * Glassmorphism Card with Hover Effects
 * FROSTED GLASS, BACKDROP BLUR, SMOOTH ANIMATIONS
 */
const GlassCard = ({ 
    children, 
    title, 
    icon,
    action,
    delay = 0,
    className = ''
}) => {
    return (
        <motion.div
            className={`glass-card ${className}`}
            initial={{ opacity: 0, y: 30 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ 
                duration: 0.5, 
                delay: delay,
                ease: "easeOut"
            }}
            whileHover={{ 
                y: -4,
                transition: { duration: 0.2 }
            }}
        >
            {(title || icon || action) && (
                <div className="glass-card-header">
                    <div style={{ display: 'flex', alignItems: 'center', gap: '0.75rem' }}>
                        {icon && (
                            <motion.i 
                                className={icon}
                                style={{ fontSize: '1.5rem', color: '#667eea' }}
                                whileHover={{ 
                                    rotate: 360,
                                    scale: 1.2
                                }}
                                transition={{ duration: 0.5 }}
                            />
                        )}
                        {title && <h3>{title}</h3>}
                    </div>
                    {action && (
                        <motion.div
                            whileHover={{ scale: 1.1 }}
                            whileTap={{ scale: 0.95 }}
                        >
                            {action}
                        </motion.div>
                    )}
                </div>
            )}
            
            <div className="glass-card-body">
                {children}
            </div>
        </motion.div>
    );
};

export default GlassCard;
