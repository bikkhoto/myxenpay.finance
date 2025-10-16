<?php
// Retail & Commerce Integration for MyxenPay
// This file handles retail and commerce specific functionality

session_start();

// Database configuration (in production, use environment variables)
$host = 'localhost';
$dbname = 'myxenpay';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    // In production, log this error instead of displaying it
    die("Connection failed: " . $e->getMessage());
}

// Handle different request methods
$method = $_SERVER['REQUEST_METHOD'];

switch($method) {
    case 'GET':
        handleGetRequest();
        break;
    case 'POST':
        handlePostRequest();
        break;
    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
}

function handleGetRequest() {
    global $pdo;
    
    $action = $_GET['action'] ?? 'dashboard';
    
    switch($action) {
        case 'dashboard':
            showDashboard();
            break;
        case 'qr-generate':
            generateQRCode();
            break;
        case 'transactions':
            getTransactions();
            break;
        case 'analytics':
            getAnalytics();
            break;
        default:
            showDashboard();
    }
}

function handlePostRequest() {
    global $pdo;
    
    $action = $_POST['action'] ?? '';
    
    switch($action) {
        case 'create-merchant':
            createMerchant();
            break;
        case 'process-payment':
            processPayment();
            break;
        case 'update-settings':
            updateSettings();
            break;
        default:
            http_response_code(400);
            echo json_encode(['error' => 'Invalid action']);
    }
}

function showDashboard() {
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Retail & Commerce - MyxenPay</title>
        <style>
            :root {
                --bg: #ffffff;
                --text: #111111;
                --card-bg: rgba(255, 255, 255, 0.7);
                --border: rgba(0, 0, 0, 0.05);
                --primary: #007AFF;
                --secondary: #30D158;
                --accent: #FF9F0A;
                --header-bg: rgba(255, 255, 255, 0.8);
                --shadow: 0 8px 32px rgba(0, 0, 0, 0.08);
            }

            [data-theme="dark"] {
                --bg: #000000;
                --text: #f5f5f7;
                --card-bg: rgba(30, 30, 30, 0.7);
                --border: rgba(255, 255, 255, 0.05);
                --primary: #0A84FF;
                --secondary: #32D74B;
                --accent: #FF9F0A;
                --header-bg: rgba(0, 0, 0, 0.8);
                --shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
            }

            * {
                margin: 0;
                padding: 0;
                box-sizing: border-box;
            }

            body {
                font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
                background: var(--bg);
                color: var(--text);
                line-height: 1.6;
            }

            .container {
                max-width: 1200px;
                margin: 0 auto;
                padding: 0 20px;
            }

            .hero {
                padding: 80px 0;
                text-align: center;
                background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
                color: white;
            }

            .hero h1 {
                font-size: 3rem;
                font-weight: 800;
                margin-bottom: 1rem;
            }

            .hero p {
                font-size: 1.2rem;
                opacity: 0.9;
                margin-bottom: 2rem;
            }

            .main-content {
                padding: 80px 0;
            }

            .features-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
                gap: 2rem;
                margin: 3rem 0;
            }

            .feature-card {
                background: var(--card-bg);
                padding: 2rem;
                border-radius: 16px;
                border: 1px solid var(--border);
                backdrop-filter: blur(10px);
                transition: all 0.3s ease;
                text-align: center;
            }

            .feature-card:hover {
                transform: translateY(-5px);
                box-shadow: var(--shadow);
            }

            .feature-icon {
                font-size: 3rem;
                margin-bottom: 1rem;
            }

            .feature-card h3 {
                font-size: 1.5rem;
                font-weight: 600;
                margin-bottom: 1rem;
                color: var(--text);
            }

            .feature-card p {
                color: var(--text);
                opacity: 0.8;
                margin-bottom: 1.5rem;
            }

            .action-btn {
                display: inline-flex;
                align-items: center;
                gap: 0.5rem;
                padding: 1rem 2rem;
                background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
                color: white;
                text-decoration: none;
                border-radius: 12px;
                font-weight: 600;
                font-size: 1.1rem;
                transition: all 0.3s ease;
                box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            }

            .action-btn:hover {
                transform: translateY(-2px);
                box-shadow: 0 8px 30px rgba(0, 0, 0, 0.15);
            }

            .integration-section {
                background: var(--card-bg);
                padding: 3rem;
                border-radius: 20px;
                border: 1px solid var(--border);
                margin: 3rem 0;
                text-align: center;
            }

            .integration-section h2 {
                font-size: 2rem;
                font-weight: 700;
                margin-bottom: 1rem;
                color: var(--text);
            }

            .integration-section p {
                color: var(--text);
                opacity: 0.8;
                margin-bottom: 2rem;
            }

            .code-block {
                background: var(--bg);
                padding: 1.5rem;
                border-radius: 12px;
                border: 1px solid var(--border);
                text-align: left;
                margin: 2rem 0;
                font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', monospace;
                font-size: 0.9rem;
                overflow-x: auto;
            }

            .theme-toggle {
                position: fixed;
                top: 20px;
                right: 20px;
                background: none;
                border: 2px solid var(--primary);
                color: var(--primary);
                padding: 8px 16px;
                border-radius: 8px;
                cursor: pointer;
                font-weight: 600;
                transition: all 0.3s ease;
                z-index: 1000;
            }

            .theme-toggle:hover {
                background: var(--primary);
                color: var(--bg);
            }
        </style>
    </head>
    <body>
        <button class="theme-toggle" onclick="toggleTheme()">🌙</button>

        <section class="hero">
            <div class="container">
                <h1>🏪 Retail & Commerce</h1>
                <p>Revolutionize your retail business with instant crypto payments</p>
            </div>
        </section>

        <section class="main-content">
            <div class="container">
                <div class="features-grid">
                    <div class="feature-card">
                        <div class="feature-icon">🛒</div>
                        <h3>Point of Sale Integration</h3>
                        <p>Seamlessly integrate MyxenPay with your existing POS systems for instant crypto payments.</p>
                        <a href="merchant.html" class="action-btn">Get Started</a>
                    </div>
                    <div class="feature-card">
                        <div class="feature-icon">📱</div>
                        <h3>Mobile Payments</h3>
                        <p>Accept payments on mobile devices with our responsive QR code system.</p>
                        <a href="merchant.html" class="action-btn">Learn More</a>
                    </div>
                    <div class="feature-card">
                        <div class="feature-icon">🔄</div>
                        <h3>Inventory Sync</h3>
                        <p>Automatically sync payment data with your inventory management system.</p>
                        <a href="merchant.html" class="action-btn">View Integration</a>
                    </div>
                </div>

                <div class="integration-section">
                    <h2>Easy Integration</h2>
                    <p>Integrate MyxenPay with your existing retail systems in minutes</p>
                    
                    <div class="code-block">
// Example: Basic payment processing
$payment = new MyxenPayPayment();
$payment->setAmount(25.50);
$payment->setCurrency('USDC');
$payment->setMerchantId('your_merchant_id');

$result = $payment->process();
if ($result->success) {
    echo "Payment successful: " . $result->transactionId;
} else {
    echo "Payment failed: " . $result->error;
}
                    </div>

                    <a href="merchant.html" class="action-btn">Start Integration</a>
                </div>

                <div class="features-grid">
                    <div class="feature-card">
                        <div class="feature-icon">📊</div>
                        <h3>Real-time Analytics</h3>
                        <p>Track sales, customer behavior, and payment trends in real-time.</p>
                        <a href="merchant-dashboard.html" class="action-btn">View Dashboard</a>
                    </div>
                    <div class="feature-card">
                        <div class="feature-icon">🔒</div>
                        <h3>Secure Transactions</h3>
                        <p>Bank-level security with blockchain transparency for all transactions.</p>
                        <a href="merchant.html" class="action-btn">Security Details</a>
                    </div>
                    <div class="feature-card">
                        <div class="feature-icon">🌍</div>
                        <h3>Global Reach</h3>
                        <p>Accept payments from customers worldwide without currency restrictions.</p>
                        <a href="merchant.html" class="action-btn">Learn More</a>
                    </div>
                </div>
            </div>
        </section>

        <script>
            function toggleTheme() {
                const body = document.body;
                const currentTheme = body.getAttribute('data-theme');
                const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
                
                body.setAttribute('data-theme', newTheme);
                localStorage.setItem('theme', newTheme);
                
                const button = document.querySelector('.theme-toggle');
                button.textContent = newTheme === 'dark' ? '☀️' : '🌙';
            }

            // Load saved theme
            document.addEventListener('DOMContentLoaded', function() {
                const savedTheme = localStorage.getItem('theme') || 'light';
                document.body.setAttribute('data-theme', savedTheme);
                
                const button = document.querySelector('.theme-toggle');
                button.textContent = savedTheme === 'dark' ? '☀️' : '🌙';
            });
        </script>
    </body>
    </html>
    <?php
}

function generateQRCode() {
    $merchantId = $_GET['merchant_id'] ?? '';
    $amount = $_GET['amount'] ?? '';
    $currency = $_GET['currency'] ?? 'SOL';
    
    if (empty($merchantId)) {
        http_response_code(400);
        echo json_encode(['error' => 'Merchant ID required']);
        return;
    }
    
    // In a real implementation, this would generate an actual QR code
    $qrData = [
        'merchant_id' => $merchantId,
        'amount' => $amount,
        'currency' => $currency,
        'timestamp' => time(),
        'qr_url' => 'https://myxenpay.finance/pay/' . bin2hex(random_bytes(16))
    ];
    
    echo json_encode($qrData);
}

function getTransactions() {
    global $pdo;
    
    $merchantId = $_GET['merchant_id'] ?? '';
    $limit = $_GET['limit'] ?? 50;
    
    if (empty($merchantId)) {
        http_response_code(400);
        echo json_encode(['error' => 'Merchant ID required']);
        return;
    }
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM transactions WHERE merchant_id = ? ORDER BY created_at DESC LIMIT ?");
        $stmt->execute([$merchantId, $limit]);
        $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode($transactions);
    } catch(PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Database error']);
    }
}

function getAnalytics() {
    global $pdo;
    
    $merchantId = $_GET['merchant_id'] ?? '';
    $period = $_GET['period'] ?? '7d';
    
    if (empty($merchantId)) {
        http_response_code(400);
        echo json_encode(['error' => 'Merchant ID required']);
        return;
    }
    
    // In a real implementation, this would calculate actual analytics
    $analytics = [
        'total_revenue' => 12450.00,
        'total_transactions' => 247,
        'average_transaction' => 50.40,
        'success_rate' => 98.5,
        'top_currency' => 'SOL',
        'revenue_by_currency' => [
            'SOL' => 8500.00,
            'USDC' => 3200.00,
            'MYXEN' => 750.00
        ]
    ];
    
    echo json_encode($analytics);
}

function createMerchant() {
    global $pdo;
    
    $businessName = $_POST['business_name'] ?? '';
    $walletAddress = $_POST['wallet_address'] ?? '';
    $email = $_POST['email'] ?? '';
    
    if (empty($businessName) || empty($walletAddress) || empty($email)) {
        http_response_code(400);
        echo json_encode(['error' => 'All fields required']);
        return;
    }
    
    try {
        $merchantId = bin2hex(random_bytes(16));
        $stmt = $pdo->prepare("INSERT INTO merchants (id, business_name, wallet_address, email, created_at) VALUES (?, ?, ?, ?, NOW())");
        $stmt->execute([$merchantId, $businessName, $walletAddress, $email]);
        
        echo json_encode(['merchant_id' => $merchantId, 'status' => 'success']);
    } catch(PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Database error']);
    }
}

function processPayment() {
    global $pdo;
    
    $merchantId = $_POST['merchant_id'] ?? '';
    $amount = $_POST['amount'] ?? '';
    $currency = $_POST['currency'] ?? 'SOL';
    $customerWallet = $_POST['customer_wallet'] ?? '';
    
    if (empty($merchantId) || empty($amount) || empty($customerWallet)) {
        http_response_code(400);
        echo json_encode(['error' => 'Required fields missing']);
        return;
    }
    
    // In a real implementation, this would process the actual Solana transaction
    $transactionId = bin2hex(random_bytes(16));
    $status = 'completed'; // In real implementation, this would be determined by blockchain confirmation
    
    try {
        $stmt = $pdo->prepare("INSERT INTO transactions (id, merchant_id, amount, currency, customer_wallet, status, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
        $stmt->execute([$transactionId, $merchantId, $amount, $currency, $customerWallet, $status]);
        
        echo json_encode([
            'transaction_id' => $transactionId,
            'status' => $status,
            'amount' => $amount,
            'currency' => $currency
        ]);
    } catch(PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Database error']);
    }
}

function updateSettings() {
    global $pdo;
    
    $merchantId = $_POST['merchant_id'] ?? '';
    $settings = $_POST['settings'] ?? [];
    
    if (empty($merchantId)) {
        http_response_code(400);
        echo json_encode(['error' => 'Merchant ID required']);
        return;
    }
    
    try {
        $settingsJson = json_encode($settings);
        $stmt = $pdo->prepare("UPDATE merchants SET settings = ?, updated_at = NOW() WHERE id = ?");
        $stmt->execute([$settingsJson, $merchantId]);
        
        echo json_encode(['status' => 'success']);
    } catch(PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Database error']);
    }
}
?>
