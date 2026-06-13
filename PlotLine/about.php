<?php
include_once("vendor/autoload.php");
use Helpers\Auth;
$currentUser = Auth::check();
$currentPage = 'about.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us | Novela</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        :root {
            --primary-color: #0076fc;
            --dark-slate: #1e293b;
        }

        body { background-color: #ffffff; font-family: 'Inter', sans-serif; }

        /* Hero Section */
        .about-hero {
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            padding: 100px 0;
            text-align: center;
            border-bottom: 1px solid #e2e8f0;
        }

        .hero-title {
            font-size: 3.5rem;
            font-weight: 900;
            color: var(--dark-slate);
            letter-spacing: -1px;
        }

        /* Mission Section */
        .mission-box {
            background: #fff;
            border-radius: 24px;
            padding: 40px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.05);
            margin-top: -60px;
            border: 1px solid #f1f5f9;
        }

        /* Feature Cards */
        .feature-icon {
            width: 60px;
            height: 60px;
            background: #f0f7ff;
            color: var(--primary-color);
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 16px;
            font-size: 1.5rem;
            margin-bottom: 20px;
        }

        .stat-card {
            border-radius: 20px;
            padding: 30px;
            background: var(--dark-slate);
            color: white;
            height: 100%;
        }

        .active-page {
            color: var(--primary-color) !important;
            font-weight: 700;
        }

        footer {
            background: #f8fafc;
            padding: 60px 0;
            margin-top: 100px;
            border-top: 1px solid #e2e8f0;
        }
    </style>
</head>
<body>

    <?php include 'components/navbar.php'; ?>

    <section class="about-hero">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <h1 class="hero-title mb-3">Where Stories <span class="text-primary">Find Their Voice.</span></h1>
                    <p class="lead text-muted fs-4">Novela is a social publishing platform that connects a global community of readers and writers through the power of story.</p>
                </div>
            </div>
        </div>
    </section>

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="mission-box mb-5">
                    <div class="row align-items-center">
                        <div class="col-md-7">
                            <h2 class="fw-bold mb-4">Our Mission</h2>
                            <p class="text-secondary lh-lg">
                                We believe that everyone has a story to tell. Novela was built to break down the barriers of traditional publishing, allowing authors to share their narratives directly with an audience that craves them. Whether you're a seasoned novelist or just starting your first chapter, Novela provides the tools to grow your craft and your community.
                            </p>
                        </div>
                        <div class="col-md-5 text-center">
                            <img src="https://illustrations.popsy.co/slate/writing.svg" alt="Writing Illustration" style="max-width: 80%;">
                        </div>
                    </div>
                </div>

                <div class="row g-4 mt-5">
                    <div class="col-md-4">
                        <div class="feature-icon"><i class="bi bi-pencil-fill"></i></div>
                        <h5 class="fw-bold">Write Freely</h5>
                        <p class="text-muted small">Our intuitive editor lets you focus on what matters most: your storytelling. Manage drafts and publish chapters with ease.</p>
                    </div>
                    <div class="col-md-4">
                        <div class="feature-icon"><i class="bi bi-people-fill"></i></div>
                        <h5 class="fw-bold">Build Community</h5>
                        <p class="text-muted small">Follow your favorite authors, leave ratings, and bookmark stories to build a library that reflects your taste.</p>
                    </div>
                    <div class="col-md-4">
                        <div class="feature-icon"><i class="bi bi-shield-check"></i></div>
                        <h5 class="fw-bold">Safe Space</h5>
                        <p class="text-muted small">We prioritize a respectful environment for creators to share their work and for readers to explore new worlds.</p>
                    </div>
                </div>

                <div class="row g-4 mt-5 mb-5">
                    <div class="col-md-6">
                        <div class="stat-card">
                            <h3 class="display-5 fw-bold text-primary">100%</h3>
                            <p class="mb-0 text-white-50">Free for independent creators to publish their works without hidden fees.</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="stat-card" style="background: #f1f5f9; color: var(--dark-slate);">
                            <h3 class="display-5 fw-bold text-primary">Global</h3>
                            <p class="mb-0 text-muted">A diverse library featuring genres from around the world, accessible from anywhere.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <footer>
        <div class="container text-center">
            <h5 class="fw-bold mb-3">Novela</h5>
            <div class="d-flex justify-content-center gap-3 mb-4">
                <a href="#" class="text-secondary fs-4"><i class="bi bi-twitter-x"></i></a>
                <a href="#" class="text-secondary fs-4"><i class="bi bi-instagram"></i></a>
                <a href="#" class="text-secondary fs-4"><i class="bi bi-github"></i></a>
            </div>
            <p class="text-muted small">&copy; 2026 Novela Inc. Built with love for storytellers.</p>
        </div>
    </footer>
</body>
</html>