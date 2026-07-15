</main>
<footer class="bg-dark text-light py-4 mt-5">
    <div class="container">
        <h2 class="h5">Nos horaires</h2>
        <ul class="list-unstyled">
            <?php
            $horaires = $pdo->query('SELECT jour, heure_ouverture, heure_fermeture FROM horaire')->fetchAll();
            foreach ($horaires as $h) {
                echo '<li>' . ucfirst($h['jour']) . ' : '
                    . substr($h['heure_ouverture'], 0, 5) . ' - '
                    . substr($h['heure_fermeture'], 0, 5) . '</li>';
            }
            ?>
        </ul>
        <p class="mb-0">
            <a class="link-light" href="<?= $basePath ?>mentions-legales.php">Mentions légales</a> ·
            <a class="link-light" href="<?= $basePath ?>cgv.php">Conditions générales de vente</a>
        </p>
    </div>
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>