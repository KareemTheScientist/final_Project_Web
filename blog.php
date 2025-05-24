<?php
require_once 'config/init.php';
require_once 'includes/navbar.php';

// Fetch blog posts from database
try {
    $stmt = $pdo->query("
        SELECT p.*, u.username as author_name, 
               (SELECT COUNT(*) FROM comments WHERE post_id = p.id) as comment_count
        FROM posts p
        JOIN users u ON p.author_id = u.id
        WHERE p.status = 'published'
        ORDER BY p.created_at DESC
    ");
    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error fetching blog posts: " . $e->getMessage());
    $posts = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blog - Nabta</title>
    <link rel="icon" type="image/png" href="img/NABTA.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary: #2e7d32;
            --primary-light: #4CAF50;
            --primary-dark: #1b5e20;
            --dark: #263238;
            --light: #f5f5f6;
            --gray: #607d8b;
            --white: #ffffff;
            --shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: var(--dark);
            background-color: var(--light);
            margin: 0;
            padding-top: 80px;
        }

        .blog-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }

        .blog-header {
            text-align: center;
            margin-bottom: 3rem;
        }

        .blog-header h1 {
            font-size: 2.5rem;
            color: var(--primary);
            margin-bottom: 1rem;
        }

        .blog-header p {
            color: var(--gray);
            font-size: 1.1rem;
            max-width: 600px;
            margin: 0 auto;
        }

        .blog-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 2rem;
            margin-bottom: 3rem;
        }

        .blog-card {
            background: var(--white);
            border-radius: 10px;
            overflow: hidden;
            box-shadow: var(--shadow);
            transition: transform 0.3s ease;
        }

        .blog-card:hover {
            transform: translateY(-5px);
        }

        .blog-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }

        .blog-content {
            padding: 1.5rem;
        }

        .blog-category {
            display: inline-block;
            background: var(--primary-light);
            color: var(--white);
            padding: 0.3rem 0.8rem;
            border-radius: 20px;
            font-size: 0.8rem;
            margin-bottom: 1rem;
        }

        .blog-title {
            font-size: 1.3rem;
            margin: 0 0 1rem 0;
            color: var(--dark);
        }

        .blog-title a {
            color: inherit;
            text-decoration: none;
            transition: color 0.3s;
        }

        .blog-title a:hover {
            color: var(--primary);
        }

        .blog-excerpt {
            color: var(--gray);
            font-size: 0.95rem;
            margin-bottom: 1.5rem;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .blog-meta {
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 0.85rem;
            color: var(--gray);
            border-top: 1px solid #eee;
            padding-top: 1rem;
        }

        .blog-author {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .author-avatar {
            width: 30px;
            height: 30px;
            background: var(--primary);
            color: var(--white);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }

        .blog-stats {
            display: flex;
            gap: 1rem;
        }

        .blog-stat {
            display: flex;
            align-items: center;
            gap: 0.3rem;
        }

        .pagination {
            display: flex;
            justify-content: center;
            gap: 0.5rem;
            margin-top: 2rem;
        }

        .pagination a {
            padding: 0.5rem 1rem;
            background: var(--white);
            border-radius: 5px;
            text-decoration: none;
            color: var(--dark);
            transition: all 0.3s;
        }

        .pagination a:hover,
        .pagination a.active {
            background: var(--primary);
            color: var(--white);
        }

        @media (max-width: 768px) {
            .blog-container {
                padding: 1rem;
            }

            .blog-header h1 {
                font-size: 2rem;
            }

            .blog-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="blog-container">
        <div class="blog-header">
            <h1>Nabta Blog</h1>
            <p>Discover the latest insights, tips, and stories about sustainable gardening and smart farming.</p>
        </div>

        <div class="blog-grid">
            <?php foreach ($posts as $post): ?>
                <article class="blog-card">
                    <img src="<?= htmlspecialchars($post['featured_image'] ?? 'img/blog-default.jpg') ?>" 
                         alt="<?= htmlspecialchars($post['title']) ?>" 
                         class="blog-image">
                    <div class="blog-content">
                        <span class="blog-category"><?= htmlspecialchars($post['category']) ?></span>
                        <h2 class="blog-title">
                            <a href="blog-post.php?id=<?= $post['id'] ?>">
                                <?= htmlspecialchars($post['title']) ?>
                            </a>
                        </h2>
                        <p class="blog-excerpt">
                            <?= htmlspecialchars(substr($post['content'], 0, 150)) ?>...
                        </p>
                        <div class="blog-meta">
                            <div class="blog-author">
                                <div class="author-avatar">
                                    <?= strtoupper(substr($post['author_name'], 0, 1)) ?>
                                </div>
                                <span><?= htmlspecialchars($post['author_name']) ?></span>
                            </div>
                            <div class="blog-stats">
                                <span class="blog-stat">
                                    <i class="far fa-calendar"></i>
                                    <?= date('M d, Y', strtotime($post['created_at'])) ?>
                                </span>
                                <span class="blog-stat">
                                    <i class="far fa-comment"></i>
                                    <?= $post['comment_count'] ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>

        <div class="pagination">
            <a href="#" class="active">1</a>
            <a href="#">2</a>
            <a href="#">3</a>
            <a href="#"><i class="fas fa-chevron-right"></i></a>
        </div>
    </div>

    <script>
        // Add smooth scrolling for pagination
        document.querySelectorAll('.pagination a').forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                // Add your pagination logic here
            });
        });
    </script>
</body>
</html> 