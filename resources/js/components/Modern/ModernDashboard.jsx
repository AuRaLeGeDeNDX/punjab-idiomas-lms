import React, { useState } from 'react';
import { motion } from 'framer-motion';
import AnimatedStatCard from './AnimatedStatCard';
import GlassCard from './GlassCard';
import ModernButton from './ModernButton';
import AnimatedBackground from './AnimatedBackground';
import AnimatedChart from './AnimatedChart';

/**
 * Modern Dashboard Component
 * COMPLETE DASHBOARD WITH ALL MODERN ELEMENTS
 */
const ModernDashboard = ({ stats, activities, alerts, quickActions }) => {
    const [activeTab, setActiveTab] = useState('overview');

    // Sample chart data
    const chartData = {
        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
        datasets: [{
            label: 'User Growth',
            data: [12, 19, 15, 25, 22, 30]
        }]
    };

    return (
        <div style={{ minHeight: '100vh', background: '#f8f9fa' }}>
            {/* Hero Section with Animated Background */}
            <AnimatedBackground>
                <div style={{ padding: '4rem 2rem', textAlign: 'center' }}>
                    <motion.h1
                        style={{
                            fontSize: '4rem',
                            fontWeight: '900',
                            background: 'linear-gradient(135deg, #ffffff 0%, rgba(255,255,255,0.8) 100%)',
                            WebkitBackgroundClip: 'text',
                            WebkitTextFillColor: 'transparent',
                            marginBottom: '1rem',
                            textShadow: '0 2px 20px rgba(0,0,0,0.1)'
                        }}
                        initial={{ opacity: 0, y: -50 }}
                        animate={{ opacity: 1, y: 0 }}
                        transition={{ duration: 0.8 }}
                    >
                        Welcome Back! 👋
                    </motion.h1>
                    
                    <motion.p
                        style={{
                            fontSize: '1.25rem',
                            color: 'rgba(255, 255, 255, 0.9)',
                            maxWidth: '600px',
                            margin: '0 auto'
                        }}
                        initial={{ opacity: 0 }}
                        animate={{ opacity: 1 }}
                        transition={{ delay: 0.3, duration: 0.8 }}
                    >
                        Here's what's happening with your platform today
                    </motion.p>
                </div>
            </AnimatedBackground>

            {/* Main Content */}
            <div style={{ padding: '2rem', marginTop: '-4rem', position: 'relative', zIndex: 10 }}>
                {/* Stats Grid */}
                <div style={{ 
                    display: 'grid', 
                    gridTemplateColumns: 'repeat(auto-fit, minmax(250px, 1fr))',
                    gap: '2rem',
                    marginBottom: '3rem'
                }}>
                    <AnimatedStatCard
                        icon="fas fa-users"
                        number={stats?.totalUsers || 1234}
                        label="Total Users"
                        subtitle={`${stats?.activeUsers || 856} active`}
                        variant="primary"
                        delay={0}
                    />
                    <AnimatedStatCard
                        icon="fas fa-book"
                        number={stats?.totalCourses || 45}
                        label="Total Courses"
                        subtitle={`${stats?.publishedCourses || 38} published`}
                        variant="success"
                        delay={0.1}
                    />
                    <AnimatedStatCard
                        icon="fas fa-user-graduate"
                        number={stats?.totalEnrollments || 3456}
                        label="Enrollments"
                        subtitle={`${stats?.activeEnrollments || 2890} active`}
                        variant="info"
                        delay={0.2}
                    />
                    <AnimatedStatCard
                        icon="fas fa-chart-line"
                        number={stats?.systemHealth || 98}
                        label="System Health"
                        subtitle="All systems operational"
                        variant="warning"
                        delay={0.3}
                    />
                </div>

                {/* Content Grid */}
                <div style={{ 
                    display: 'grid', 
                    gridTemplateColumns: 'repeat(auto-fit, minmax(400px, 1fr))',
                    gap: '2rem'
                }}>
                    {/* Chart Card */}
                    <GlassCard
                        title="User Growth"
                        icon="fas fa-chart-area"
                        delay={0.4}
                    >
                        <AnimatedChart
                            type="line"
                            data={chartData}
                            height={300}
                        />
                    </GlassCard>

                    {/* Quick Actions */}
                    <GlassCard
                        title="Quick Actions"
                        icon="fas fa-bolt"
                        delay={0.5}
                    >
                        <div style={{ display: 'flex', flexDirection: 'column', gap: '1rem' }}>
                            {quickActions?.map((action, index) => (
                                <ModernButton
                                    key={index}
                                    icon={action.icon}
                                    variant={action.variant || 'primary'}
                                    onClick={action.onClick}
                                >
                                    {action.label}
                                </ModernButton>
                            )) || (
                                <>
                                    <ModernButton icon="fas fa-user-plus" variant="primary">
                                        Add New User
                                    </ModernButton>
                                    <ModernButton icon="fas fa-plus" variant="success">
                                        Create Course
                                    </ModernButton>
                                    <ModernButton icon="fas fa-bullhorn" variant="warning">
                                        Send Announcement
                                    </ModernButton>
                                    <ModernButton icon="fas fa-chart-bar" variant="info">
                                        Generate Report
                                    </ModernButton>
                                </>
                            )}
                        </div>
                    </GlassCard>

                    {/* Recent Activity */}
                    <GlassCard
                        title="Recent Activity"
                        icon="fas fa-history"
                        delay={0.6}
                    >
                        <div style={{ display: 'flex', flexDirection: 'column', gap: '1rem' }}>
                            {activities?.map((activity, index) => (
                                <motion.div
                                    key={index}
                                    style={{
                                        padding: '1rem',
                                        background: 'rgba(102, 126, 234, 0.05)',
                                        borderRadius: '12px',
                                        borderLeft: '4px solid #667eea'
                                    }}
                                    initial={{ opacity: 0, x: -20 }}
                                    animate={{ opacity: 1, x: 0 }}
                                    transition={{ delay: 0.7 + index * 0.1 }}
                                    whileHover={{ x: 4 }}
                                >
                                    <div style={{ fontWeight: '600', marginBottom: '0.25rem' }}>
                                        {activity.user}
                                    </div>
                                    <div style={{ fontSize: '0.875rem', color: '#666' }}>
                                        {activity.action}
                                    </div>
                                    <div style={{ fontSize: '0.75rem', color: '#999', marginTop: '0.25rem' }}>
                                        {activity.time}
                                    </div>
                                </motion.div>
                            )) || (
                                <div style={{ textAlign: 'center', color: '#999', padding: '2rem' }}>
                                    No recent activity
                                </div>
                            )}
                        </div>
                    </GlassCard>

                    {/* System Alerts */}
                    <GlassCard
                        title="System Alerts"
                        icon="fas fa-exclamation-triangle"
                        delay={0.7}
                    >
                        <div style={{ display: 'flex', flexDirection: 'column', gap: '1rem' }}>
                            {alerts?.map((alert, index) => (
                                <motion.div
                                    key={index}
                                    style={{
                                        padding: '1rem',
                                        background: alert.type === 'warning' 
                                            ? 'rgba(250, 112, 154, 0.1)' 
                                            : 'rgba(56, 239, 125, 0.1)',
                                        borderRadius: '12px',
                                        borderLeft: `4px solid ${alert.type === 'warning' ? '#fa709a' : '#38ef7d'}`
                                    }}
                                    initial={{ opacity: 0, scale: 0.9 }}
                                    animate={{ opacity: 1, scale: 1 }}
                                    transition={{ delay: 0.8 + index * 0.1 }}
                                >
                                    <div style={{ fontWeight: '600', marginBottom: '0.25rem' }}>
                                        {alert.title}
                                    </div>
                                    <div style={{ fontSize: '0.875rem', color: '#666' }}>
                                        {alert.message}
                                    </div>
                                </motion.div>
                            )) || (
                                <div style={{ textAlign: 'center', color: '#999', padding: '2rem' }}>
                                    No alerts
                                </div>
                            )}
                        </div>
                    </GlassCard>
                </div>
            </div>
        </div>
    );
};

export default ModernDashboard;
