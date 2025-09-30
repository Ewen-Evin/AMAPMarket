document.addEventListener('DOMContentLoaded', function () {
    console.log("clientId (depuis JS) :", window.clientId);

    // Vérifie si le client est connecté
    if (window.clientId && window.clientId !== "null") {
        var boutonspanier = document.querySelectorAll('.panier');

        boutonspanier.forEach(function (bouton) {
            bouton.addEventListener('click', function () {
                var product = bouton.getAttribute('data-product');
                var price = bouton.getAttribute('data-price');
                var variety = bouton.getAttribute('data-variety');

                fetch('add_to_cart.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        product: product,
                        price: price,
                        variety: variety,
                        clientId: window.clientId // Utilisation du clientId défini dans le script HTML
                    })
                })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                title: 'Ce produit est ajouté à votre panier !',
                                text: 'Merci de votre achat',
                                icon: 'success',
                                confirmButtonColor: '#3085d6',
                                confirmButtonText: 'OK'
                            });
                        } else {
                            Swal.fire({
                                title: 'Erreur',
                                text: data.message || 'Une erreur est survenue lors de l\'ajout au panier.',
                                icon: 'error',
                                confirmButtonColor: '#d33',
                                confirmButtonText: 'OK'
                            });
                        }
                    })
                    .catch(error => {
                        Swal.fire({
                            title: 'Erreur',
                            text: 'Une erreur est survenue lors de l\'ajout au panier.',
                            icon: 'error',
                            confirmButtonColor: '#d33',
                            confirmButtonText: 'OK'
                        });
                        console.error("Erreur dans la requête fetch :", error);
                    });
            });
        });
    } else {
        // Si le client n'est pas connecté
        var boutonspanier = document.querySelectorAll('.panier');

        boutonspanier.forEach(function (bouton) {
            bouton.addEventListener('click', function () {
                Swal.fire({
                    title: 'Connexion requise',
                    text: 'Vous devez être connecté pour ajouter un produit au panier.',
                    icon: 'warning',
                    confirmButtonColor: '#d33',
                    confirmButtonText: 'OK'
                });
            });
        });
    }
});

// Gestion des formulaires (connexion / inscription)
function toggleForm(formType) {
    document.getElementById('connexion').style.display = formType === 'connexion' ? 'block' : 'none';
    document.getElementById('inscription').style.display = formType === 'inscription' ? 'block' : 'none';
}
