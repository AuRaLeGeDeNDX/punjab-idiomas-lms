import React from 'react';
import { motion } from 'framer-motion';

/**
 * Modern Button with Gradient and Animations
 * BOLD GRADIENTS, SHINE EFFECT, 3D HOVER
 */
const ModernButton = ({ 
    children, 
    icon,
    variant = 'primary',
    size = 'md',
    onClick,
    type = 'button',
    disabled = false,
    className = ''
}) => {
    const variants = {
        primary: 'modern-btn',
        success: 'modern-btn btn-success',
        warning: 'modern-btn btn-warning',
        danger: 'modern-btn btn-danger',
        info: 'modern-btn btn-info'
    };

    const sizes = {
        sm: 'modern-btn-sm',
        md: '',
        lg: 'modern-btn-lg'
    };

    return (
        <motion.button
            className={`${variants[variant]} ${sizes[size]} ${className}`}
            onClick={onClick}
            type={type}
            disabled={disabled}
            whileHover={{ 
                y: -3,
                transition: { duration: 0.2 }
            }}
            whileTap={{ 
                scale: 0.98,
                y: -1
            }}
            initial={{ opacity: 0, scale: 0.9 }}
            animate={{ opacity: 1, scale: 1 }}
            transition={{ duration: 0.3 }}
        >
            {icon && (
                <motion.i 
                    className={icon}
                    animate={{ 
                        rotate: [0, 10, -10, 0]
                    }}
                    transition={{
                        duration: 2,
                        repeat: Infinity,
                        repeatDelay: 3
                    }}
                />
            )}
            {children}
        </motion.button>
    );
};

export default ModernButton;
