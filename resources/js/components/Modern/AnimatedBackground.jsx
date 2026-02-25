import React from 'react';
import { motion } from 'framer-motion';

/**
 * Animated Gradient Background
 * FLOWING GRADIENTS, PARTICLE EFFECTS
 */
const AnimatedBackground = ({ children, variant = 'hero' }) => {
    // Generate random particles
    const particles = Array.from({ length: 20 }, (_, i) => ({
        id: i,
        x: Math.random() * 100,
        y: Math.random() * 100,
        size: Math.random() * 4 + 2,
        duration: Math.random() * 10 + 10
    }));

    return (
        <div style={{ position: 'relative', overflow: 'hidden' }}>
            {/* Animated Gradient Background */}
            <motion.div
                className="animated-gradient-bg"
                style={{
                    position: 'absolute',
                    top: 0,
                    left: 0,
                    right: 0,
                    bottom: 0,
                    zIndex: 0
                }}
                animate={{
                    backgroundPosition: ['0% 50%', '100% 50%', '0% 50%']
                }}
                transition={{
                    duration: 15,
                    repeat: Infinity,
                    ease: "linear"
                }}
            />

            {/* Floating Particles */}
            {particles.map(particle => (
                <motion.div
                    key={particle.id}
                    style={{
                        position: 'absolute',
                        left: `${particle.x}%`,
                        top: `${particle.y}%`,
                        width: `${particle.size}px`,
                        height: `${particle.size}px`,
                        borderRadius: '50%',
                        background: 'rgba(255, 255, 255, 0.3)',
                        zIndex: 1
                    }}
                    animate={{
                        y: [0, -30, 0],
                        opacity: [0.3, 0.8, 0.3],
                        scale: [1, 1.2, 1]
                    }}
                    transition={{
                        duration: particle.duration,
                        repeat: Infinity,
                        ease: "easeInOut"
                    }}
                />
            ))}

            {/* Content */}
            <div style={{ position: 'relative', zIndex: 2 }}>
                {children}
            </div>
        </div>
    );
};

export default AnimatedBackground;
