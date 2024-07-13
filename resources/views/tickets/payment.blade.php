<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Stripe Payment</title>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        Stripe Payment Form
                    </div>
                    <div class="card-body">
                        <form id="payment-form">
                            <div id="card-element" class="form-group">
                                <!-- Stripe.js injects the Card Element -->
                            </div>
                            <div id="error-message" role="alert"></div>
                            <button  id="submit" class="btn btn-primary mt-3" >Pay Now</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Stripe.js library -->
    <script src="https://js.stripe.com/v3/"></script>

    <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>

    <!-- Bootstrap JS -->
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var stripe = Stripe('{{ env("STRIPE_KEY") }}');
            var elements = stripe.elements();
            var cardElement = elements.create('card');
            cardElement.mount('#card-element');

            var form = document.getElementById('payment-form');
            var submitButton = document.getElementById('submit');
            var errorMessage = document.getElementById('error-message');
            console.log('form', form);
            
            form.addEventListener('submit', async function(event) {
                cnosole.log('event', event)
                event.preventDefault();
                submitButton.disabled = true;

                const { token, error } = await stripe.createToken(cardElement);

                if (error) {
                    // Inform the user if there was an error
                    errorMessage.textContent = error.message;
                    submitButton.disabled = false;
                } else {
                    // Send the token to your server
                    stripeTokenHandler(token);
                }
            });

            function stripeTokenHandler(token) {
                // Insert the token ID into the form so it gets submitted to the server
                 // Insert the token ID into the form so it gets submitted to the server
                 var hiddenInput = document.createElement('input');
                hiddenInput.setAttribute('type', 'hidden');
                hiddenInput.setAttribute('name', 'stripeToken');
                hiddenInput.setAttribute('value', token.id);
                form.appendChild(hiddenInput);

                // Submit the form
                form.submit();
            }
        });
    </script>
</body>
</html>
