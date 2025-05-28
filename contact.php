
<?php require_once 'includes/header.php'; ?>

<main class="main">
    <div class="container">
        <h1 class="mb-8">Contact AgriMarket</h1>
        
        <div class="grid grid-cols-2">
            <!-- Contact Information -->
            <div class="card">
                <div class="card-body">
                    <h2 class="card-title mb-4">Get In Touch</h2>
                    
                    <div class="contact-info">
                        <svg class="contact-info-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                        </svg>
                        <div>
                            <p class="font-medium">Phone</p>
                            <a href="tel:+1234567890" class="text-gray-600">(123) 456-7890</a>
                        </div>
                    </div>
                    
                    <div class="contact-info">
                        <svg class="contact-info-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                        <div>
                            <p class="font-medium">Email</p>
                            <a href="mailto:info@agrimarket.com" class="text-gray-600">info@agrimarket.com</a>
                        </div>
                    </div>
                    
                    <div class="contact-info">
                        <svg class="contact-info-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                        <div>
                            <p class="font-medium">Address</p>
                            <p class="text-gray-600">123 Farm Street, Agriculture District, AG 12345</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contact Form -->
            <div class="card">
                <div class="card-body">
                    <h2 class="card-title mb-4">Send Us a Message</h2>
                    
                    <?php
                    // Process form submission
                    if ($_SERVER["REQUEST_METHOD"] == "POST") {
                        $name = trim($_POST["name"] ?? "");
                        $email = trim($_POST["email"] ?? "");
                        $message = trim($_POST["message"] ?? "");
                        
                        // Validate inputs
                        $errors = [];
                        
                        if (empty($name)) {
                            $errors[] = "Name is required";
                        }
                        
                        if (empty($email)) {
                            $errors[] = "Email is required";
                        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                            $errors[] = "Invalid email format";
                        }
                        
                        if (empty($message)) {
                            $errors[] = "Message is required";
                        }
                        
                        // If no errors, process the form
                        if (empty($errors)) {
                            // In a real application, you would send an email or store in database
                            // For now, just show a success message
                            echo '<div class="alert alert-success">Thank you for your message! We will get back to you soon.</div>';
                        } else {
                            // Display errors
                            echo '<div class="alert alert-error">';
                            echo '<ul>';
                            foreach ($errors as $error) {
                                echo "<li>$error</li>";
                            }
                            echo '</ul>';
                            echo '</div>';
                        }
                    }
                    ?>
                    
                    <form id="contact-form" method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                        <div class="form-group">
                            <label for="name">Name</label>
                            <input 
                                type="text" 
                                id="name" 
                                name="name" 
                                class="form-control"
                                placeholder="Your Name"
                                value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>"
                            >
                        </div>
                        
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input 
                                type="email" 
                                id="email" 
                                name="email" 
                                class="form-control"
                                placeholder="your.email@example.com"
                                value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                            >
                        </div>
                        
                        <div class="form-group">
                            <label for="message">Message</label>
                            <textarea 
                                id="message" 
                                name="message" 
                                rows="4" 
                                class="form-control"
                                placeholder="Your message here..."
                            ><?php echo isset($_POST['message']) ? htmlspecialchars($_POST['message']) : ''; ?></textarea>
                        </div>
                        
                        <button 
                            type="submit" 
                            class="btn btn-primary"
                            style="width: 100%;"
                        >
                            Send Message
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</main>

<?php require_once 'includes/footer.php'; ?>
