<?php
require_once 'config/db_connect.php';
include 'includes/header.php';
?>

<div class="hero-section" style="
    background-image: url('assets/banner.png'); 
    background-size: cover; 
    background-position: center; 
    height: 60vh; 
    display: flex; 
    align-items: center; 
    justify-content: center; 
    position: relative;
    border-radius: 1rem;
    overflow: hidden;
    margin-bottom: 2rem;
">
    <div style="
        position: absolute; 
        top: 0; 
        left: 0; 
        right: 0; 
        bottom: 0; 
        background: rgba(0,0,0,0.5);
    "></div>
    
    <div style="
        position: relative; 
        z-index: 1; 
        text-align: center; 
        color: white;
        padding: 2rem;
    ">
        <h1 style="font-size: 3.5rem; font-weight: 800; margin-bottom: 1rem; text-shadow: 2px 2px 4px rgba(0,0,0,0.5);">
            Welcome to CampusTrade
        </h1>
        <p style="font-size: 1.5rem; margin-bottom: 2rem; text-shadow: 1px 1px 2px rgba(0,0,0,0.5);">
            The ultimate marketplace for students. Buy, sell, rent, and lend with ease.
        </p>
        <a href="browse.php" class="btn btn-primary" style="
            font-size: 1.2rem; 
            padding: 1rem 2.5rem; 
            border-radius: 50px; 
            box-shadow: 0 4px 6px rgba(0,0,0,0.2);
            transition: transform 0.2s;
        " onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">
            Browse Products
        </a>
    </div>
</div>

<div class="container">
    <div class="grid grid-cols-3" style="gap: 2rem; margin-bottom: 4rem;">
        <div class="card p-4 text-center">
            <div style="font-size: 3rem; margin-bottom: 1rem;">🛍️</div>
            <h3>Buy & Sell</h3>
            <p class="text-secondary">Find great deals on textbooks, electronics, and more from fellow students.</p>
        </div>
        <div class="card p-4 text-center">
            <div style="font-size: 3rem; margin-bottom: 1rem;">🤝</div>
            <h3>Rent & Lend</h3>
            <p class="text-secondary">Need something for a short time? Rent it! Have extra gear? Lend it out.</p>
        </div>
        <div class="card p-4 text-center">
            <div style="font-size: 3rem; margin-bottom: 1rem;">🔔</div>
            <h3>Get Notified</h3>
            <p class="text-secondary">Never miss out. Get alerts when items you're looking for are posted.</p>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
