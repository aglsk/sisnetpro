<?php
require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Painel Admin - Adicionar Séries</title>
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
  <link rel="stylesheet" href="css/serie.css">
  <link rel="icon" href="assets/favicon.ico" type="image/x-icon">
</head>
<body class="dark-mode">
  <!-- Sidebar -->
  <div class="sidebar">
    <div class="sidebar-header">
      <h3><i class="fas fa-ghost" style="color:red"></i> GhostHaunt</h3>
    </div>
    <div class="sidebar-menu">
      <a href="index.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
      <a href="f.php"><i class="fas fa-plus-circle"></i> Adicionar Filme</a>
      <a href="#"><i class="fas fa-list"></i> Listar Filmes</a>
      <a href="s.php" class="active"><i class="fas fa-tv"></i> Adicionar Série</a>
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
              <h4 class="mb-0"><i class="fas fa-tv mr-2"></i>Adicionar Nova Série</h4>
            </div>
            <div class="card-body">
              <form id="postSeriesForm">
                <div class="form-row">
                  <div class="form-group col-md-6">
                    <label for="seriesSearch"><i class="fas fa-search mr-1"></i> Buscar Série</label>
                    <input type="text" class="form-control" id="seriesSearch" placeholder="Digite o nome da série" required>
                    <div id="seriesList"></div>
                  </div>
                  <div class="form-group col-md-6">
                    <label for="idTMDB"><i class="fas fa-id-card mr-1"></i> TMDB ID</label>
                    <input type="text" class="form-control" id="idTMDB" readonly>
                  </div>
                </div>
                
                <div class="form-row">
                  <div class="form-group col-md-8">
                    <label for="titulo"><i class="fas fa-heading mr-1"></i> Título</label>
                    <input type="text" class="form-control" id="titulo" readonly>
                  </div>
                  <div class="form-group col-md-4">
                    <label for="temporada"><i class="fas fa-list-ol mr-1"></i> Temporada</label>
                    <input type="number" class="form-control" id="temporada" placeholder="Nº da temporada" min="1" required>
                  </div>
                </div>
                
                <div class="form-group">
                  <label for="link"><i class="fas fa-link mr-1"></i> Link da Série</label>
                  <input type="url" class="form-control" id="link" placeholder="Digite o link da série" required>
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
                  <i class="fas fa-paper-plane mr-2"></i>Publicar Série
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
    
  const tmdbApiKey = 'fa2db16fd76770b1408ef23538da6695';
  const seriesSearchInput = document.getElementById('seriesSearch');
  const seriesList = document.getElementById('seriesList');
  const idTMDBInput = document.getElementById('idTMDB');
  const tituloInput = document.getElementById('titulo');
  const temporadaInput = document.getElementById('temporada');

  // Atualiza o título com a temporada
  temporadaInput.addEventListener('input', function () {
    const temporada = temporadaInput.value.trim();
    const baseTitulo = idTMDBInput.dataset.originalTitle || tituloInput.value;
    tituloInput.value = temporada ? `${baseTitulo} Temporada ${temporada}` : baseTitulo;
  });

  // Buscar séries da API TMDB
  seriesSearchInput.addEventListener('input', function () {
    const query = seriesSearchInput.value.trim();
    if (query.length >= 3) {
      fetch(`https://api.themoviedb.org/3/search/tv?api_key=${tmdbApiKey}&query=${query}&language=pt-BR`)
        .then(response => response.json())
        .then(data => {
          const series = data.results;
          seriesList.innerHTML = '';
          seriesList.style.display = series.length > 0 ? 'block' : 'none';

          series.forEach(serie => {
            const seriesItem = document.createElement('div');
            seriesItem.className = 'series-item';
            const posterUrl = serie.poster_path ? `https://image.tmdb.org/t/p/w500${serie.poster_path}` : 'https://via.placeholder.com/50x75?text=No+Poster';
            const genreNames = serie.genre_ids.map(id => genreMap[id] || "Desconhecido").join(', ');

            seriesItem.innerHTML = `
              <img src="${posterUrl}" alt="${serie.name}" onerror="this.src='https://via.placeholder.com/50x75?text=No+Poster'">
              <div>
                <strong>${serie.name}</strong><br>
                <small>${genreNames}</small>
              </div>
            `;

            seriesItem.addEventListener('click', function () {
              idTMDBInput.value = serie.id;
              idTMDBInput.dataset.originalTitle = serie.name;
              tituloInput.value = serie.name;
              document.getElementById('thumbnail').value = posterUrl;
              document.getElementById('fanart').value = serie.backdrop_path ? `https://image.tmdb.org/t/p/w500${serie.backdrop_path}` : '';
              document.getElementById('info').value = serie.overview || 'Nenhuma descrição disponível.';
              document.getElementById('genero').value = genreNames;
              document.getElementById('lancamento').value = serie.first_air_date || 'Data desconhecida';

              seriesList.innerHTML = '';
              seriesList.style.display = 'none';
            });

            seriesList.appendChild(seriesItem);
          });
        })
        .catch(error => {
          console.error('Erro ao buscar séries:', error);
          seriesList.innerHTML = '<div class="p-2 text-danger">Erro ao carregar séries. Tente novamente.</div>';
          seriesList.style.display = 'block';
        });
    } else {
      seriesList.innerHTML = '';
      seriesList.style.display = 'none';
    }
  });

  const genreMap = {
    10759: "Ação & Aventura",
    16: "Animação",
    35: "Comédia",
    80: "Crime",
    99: "Documentário",
    18: "Drama",
    10751: "Família",
    10762: "Kids",
    9648: "Mistério",
    10763: "Notícias",
    10764: "Reality",
    10765: "Ficção Científica & Fantasia",
    10766: "Novela",
    10767: "Talk Show",
    10768: "Guerra & Política",
    37: "Faroeste"
  };

  // Enviar dados do formulário para Google Sheets
  document.getElementById("postSeriesForm").addEventListener("submit", function (event) {
    event.preventDefault();

    const titulo = tituloInput.value.trim();
    const link = document.getElementById("link").value.trim();
    const temporada = temporadaInput.value.trim();
    const thumbnail = document.getElementById("thumbnail").value.trim();
    const fanart = document.getElementById("fanart").value.trim();
    const info = document.getElementById("info").value.trim();
    const genero = document.getElementById("genero").value.trim();
    const lancamento = document.getElementById("lancamento").value.trim();

    if (!titulo || !link || !temporada) {
      document.getElementById('responseMessage').innerHTML = '<div class="alert alert-danger">Preencha todos os campos obrigatórios!</div>';
      return;
    }

    const formData = new URLSearchParams();
    formData.append('titulo', titulo);
    formData.append('link', link);
    formData.append('temporada', temporada);
    formData.append('thumbnail', thumbnail);
    formData.append('fanart', fanart);
    formData.append('info', info);
    formData.append('genero', genero);
    formData.append('lancamento', lancamento);

    document.getElementById('responseMessage').innerHTML = '<div class="alert alert-info">Enviando dados, por favor aguarde...</div>';

    const scriptURL = "https://script.google.com/macros/s/AKfycbwqR7bT4RWWGUO2i380E_XUNLHLBVNTKOTDP14p7odsS3RqNzvLZ4q036gONAY0g91p_Q/exec";

    fetch(scriptURL, {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded"
      },
      body: formData.toString()
    })
      .then(response => response.json())
      .then(data => {
        document.getElementById('responseMessage').innerHTML = `<div class="alert alert-success">${data.message || 'Série publicada com sucesso!'}</div>`;
        document.getElementById("postSeriesForm").reset();
      })
      .catch(error => {
        document.getElementById('responseMessage').innerHTML = '<div class="alert alert-danger">Erro ao publicar a série.</div>';
      });
  });
  </script>
</body>
</html>