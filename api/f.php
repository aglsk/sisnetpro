<?php
require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Painel Admin - Postar Filmes</title>
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
  <link rel="stylesheet" href="css/filme.css">
  <link rel="icon" href="assets/favicon.ico" type="image/x-icon">
</head>
<body>
  <!-- Sidebar -->
  <div class="sidebar">
    <div class="sidebar-header">
      <h3><i class="fas fa-ghost" style="color:red"></i> GhostHaunt</h3>
    </div>
    <div class="sidebar-menu">
      <a href="index.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
      <a href="f.php" class="active" ><i class="fas fa-plus-circle"></i> Adicionar Filme</a>
      <a href="#"><i class="fas fa-list"></i> Listar Filmes</a>
      <a href="s.php"><i class="fas fa-tv"></i> Adicionar Série</a>
      <a href="#"><i class="fas fa-list-ol"></i> Listar Séries</a>
      <a href="/post"><i class="fas fa-search"></i> Post IMG</a>
      <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Sair</a>
    </div>
  </div>

  <!-- Conteúdo Principal -->
  <div class="main-content">

    <!-- Conteúdo da Página -->
    <div class="container-fluid">
      <div class="row">
        <div class="col-12">
          <div class="card">
            <div class="card-header">
              <h4 class="mb-0"><i class="fas fa-plus-circle mr-2"></i>Adicionar Novo Filme</h4>
            </div>
            <div class="card-body">
              <form id="postFilmForm">
                <div class="form-row">
                  <div class="form-group col-md-6">
                    <label for="movieSearch"><i class="fas fa-search mr-1"></i> Buscar Filme</label>
                    <input type="text" class="form-control" id="movieSearch" placeholder="Digite o nome do filme" required>
                    <div id="movieList"></div>
                  </div>
                  <div class="form-group col-md-6">
                    <label for="tmdbID"><i class="fas fa-id-card mr-1"></i> TMDb ID</label>
                    <input type="text" class="form-control" id="tmdbID" readonly>
                  </div>
                </div>
                
                <div class="form-group">
                  <label for="titulo"><i class="fas fa-heading mr-1"></i> Título</label>
                  <input type="text" class="form-control" id="titulo" readonly>
                </div>
                
                <div class="form-group">
                  <label for="link"><i class="fas fa-link mr-1"></i> Link do Filme</label>
                  <input type="url" class="form-control" id="link" placeholder="Digite o link do filme" required>
                </div>
                
                <div class="form-row">
                  <div class="form-group col-md-6">
                    <label for="thumbnail"><i class="fas fa-image mr-1"></i> Thumbnail URL</label>
                    <input type="url" class="form-control" id="thumbnail" readonly>
                  </div>
                  <div class="form-group col-md-6">
                    <label for="fanart"><i class="fas fa-images mr-1"></i> Fanart URL</label>
                    <input type="url" class="form-control" id="fanart" readonly>
                  </div>
                </div>
                
                <div class="form-group">
                  <label for="info"><i class="fas fa-info-circle mr-1"></i> Informações</label>
                  <textarea class="form-control" id="info" rows="3" readonly></textarea>
                </div>
                
                <div class="form-row">
                  <div class="form-group col-md-6">
                    <label for="genero"><i class="fas fa-tags mr-1"></i> Gênero</label>
                    <input type="text" class="form-control" id="genero" readonly>
                  </div>
                  <div class="form-group col-md-6">
                    <label for="lancamento"><i class="fas fa-calendar-alt mr-1"></i> Lançamento</label>
                    <input type="text" class="form-control" id="lancamento" readonly>
                  </div>
                </div>
                
                <button type="submit" class="btn btn-primary btn-block mt-3">
                  <i class="fas fa-paper-plane mr-2"></i>Publicar Filme
                </button>
              </form>
              <div id="responseMessage" class="mt-3"></div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
  <script>
    // Toggle sidebar em dispositivos móveis
    $('.sidebar-collapse').click(function() {
      $('.sidebar').toggleClass('active');
    });

  // Configurações do TMDB
  const tmdbApiKey = 'fa2db16fd76770b1408ef23538da6695';
  const movieSearchInput = document.getElementById('movieSearch');
  const movieList = document.getElementById('movieList');
  const tmdbIDInput = document.getElementById('tmdbID');

  // Mapeamento de gêneros
  const genreMap = {
    28: "Ação", 12: "Aventura", 16: "Animação", 35: "Comédia", 
    80: "Crime", 99: "Documentário", 18: "Drama", 10751: "Família",
    14: "Fantasia", 36: "História", 27: "Terror", 10402: "Música",
    9648: "Mistério", 10749: "Romance", 878: "Ficção Científica",
    10770: "Filme de TV", 53: "Thriller", 10752: "Guerra", 37: "Faroeste"
  };

  function getGenreNameById(id) {
    return genreMap[id] || "Desconhecido";
  }

  // Buscar filmes conforme o input
  movieSearchInput.addEventListener('input', function() {
    const query = movieSearchInput.value.trim();
    
    if (query.length >= 3) {
      fetch(`https://api.themoviedb.org/3/search/movie?api_key=${tmdbApiKey}&query=${query}&language=pt-BR`)
        .then(response => response.json())
        .then(data => {
          const movies = data.results;
          movieList.innerHTML = '';
          movieList.style.display = movies.length > 0 ? 'block' : 'none';

          movies.forEach(movie => {
            const movieItem = document.createElement('div');
            movieItem.className = 'movie-item';

            const posterUrl = movie.poster_path ? `https://image.tmdb.org/t/p/w500${movie.poster_path}` : 'https://upload.wikimedia.org/wikipedia/commons/1/14/No_Image_Available.jpg';
            const genreNames = movie.genre_ids.map(genreId => getGenreNameById(genreId)).join(', ');

            movieItem.innerHTML = `
              <img src="${posterUrl}" alt="${movie.title}" onerror="this.src='https://upload.wikimedia.org/wikipedia/commons/1/14/No_Image_Available.jpg'">
              <div>
                <strong>${movie.title}</strong><br>
                <small>${genreNames} | ${movie.release_date || 'N/A'}</small>
              </div>
            `;

            movieItem.addEventListener('click', function() {
              tmdbIDInput.value = movie.id;
              document.getElementById('titulo').value = movie.title;
              document.getElementById('thumbnail').value = movie.poster_path ? `https://image.tmdb.org/t/p/w500${movie.poster_path}` : '';
              document.getElementById('fanart').value = movie.backdrop_path ? `https://image.tmdb.org/t/p/w500${movie.backdrop_path}` : '';
              document.getElementById('info').value = movie.overview || 'Nenhuma descrição disponível.';
              document.getElementById('genero').value = genreNames;
              document.getElementById('lancamento').value = movie.release_date || 'Data desconhecida';

              movieList.innerHTML = '';
              movieList.style.display = 'none';
            });

            movieList.appendChild(movieItem);
          });
        })
        .catch(error => {
          console.error('Erro ao buscar filmes:', error);
          movieList.innerHTML = '<div class="p-2 text-danger">Erro ao carregar filmes. Tente novamente.</div>';
          movieList.style.display = 'block';
        });
    } else {
      movieList.innerHTML = '';
      movieList.style.display = 'none';
    }
  });

  // Envio do formulário
  document.getElementById("postFilmForm").addEventListener("submit", function(event) {
    event.preventDefault();

    const titulo = document.getElementById("titulo").value;
    const link = document.getElementById("link").value;

    if (!titulo || !link) {
      $('#responseMessage').html('<div class="alert alert-danger">Preencha pelo menos o título e o link do filme.</div>');
      return;
    }

    const formData = new URLSearchParams();
    formData.append('titulo', titulo);
    formData.append('link', link);
    formData.append('thumbnail', document.getElementById("thumbnail").value);
    formData.append('fanart', document.getElementById("fanart").value);
    formData.append('info', document.getElementById("info").value);
    formData.append('genero', document.getElementById("genero").value);
    formData.append('lancamento', document.getElementById("lancamento").value);

    $('#responseMessage').html('<div class="alert alert-info">Enviando dados, por favor aguarde...</div>');

    const corsProxy = 'https://apiutilities.vercel.app/api/proxy?url=';
    const scriptURL = "https://script.google.com/macros/s/AKfycbzx7m-BZDyLOyOkyiFETBxSIL_ubcuS6cWhob5PWpE83Vx_RpYAaxf6ilSUwuyVZEUYdA/exec";

    fetch(`${corsProxy}${scriptURL}`, {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded"
      },
      body: formData.toString()
    })
    .then(response => response.text())
    .then(data => {
      $('#responseMessage').html('<div class="alert alert-success">Filme publicado com sucesso!</div>');
      document.getElementById("postFilmForm").reset();
    })
    .catch(error => {
      console.error('Erro ao publicar:', error);
      $('#responseMessage').html('<div class="alert alert-danger">Erro ao publicar o filme. Tente novamente.</div>');
    });
  });
  </script>
</body>
</html>