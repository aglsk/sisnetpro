<?php
require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard - GhostHaunt</title>
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <link rel="icon" href="assets/favicon.ico" type="image/x-icon">
  <link rel="stylesheet" href="css/index.css">
</head>
<body class="dark-mode">
  <!-- Sidebar -->
  <div class="sidebar">
    <div class="sidebar-header">
      <h3><i class="fas fa-ghost" style="color:red"></i> GhostHaunt</h3>
    </div>
    <div class="sidebar-menu">
      <a href="index.php" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
      <a href="f.php"><i class="fas fa-plus-circle"></i> Adicionar Filme</a>
      <a href="#"><i class="fas fa-list"></i> Listar Filmes</a>
      <a href="s.php"><i class="fas fa-tv"></i> Adicionar Série</a>
      <a href="#"><i class="fas fa-list-ol"></i> Listar Séries</a>
      <a href="/post"><i class="fas fa-search"></i> Post IMG</a>
      <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Sair</a>
    </div>
  </div>

  <!-- Conteúdo Principal -->
  <div class="main-content">

    <!-- Conteúdo do Dashboard -->
    <div class="container-fluid">
      <h2 class="mb-4"><i class="fas fa-tachometer-alt mr-2"></i>Dashboard</h2>
      
      <!-- Gráficos e Listagens -->
      <div class="row mt-4">
        <div class="col-lg-8">
          <div class="card">
            <div class="card-header">
              <h5 class="mb-0"><i class="fas fa-chart-line mr-2"></i>Atividade Recente</h5>
            </div>
            <div class="card-body">
              <div class="chart-container">
                <canvas id="activityChart"></canvas>
              </div>
            </div>
          </div>
        </div>
        <div class="col-lg-4">
          <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
              <h5 class="mb-0"><i class="fas fa-bell mr-2"></i>Notificações</h5>
              <span class="badge badge-pill badge-danger"><?php echo $estatisticas['novos_hoje']; ?> novas</span>
            </div>
            <div class="card-body p-0">
              <div class="list-group list-group-flush">
                <?php
                // Notificações de filmes em exibição
                if(isset($movie_news['results'])) {
                    foreach(array_slice($movie_news['results'], 0, 2) as $movie) {
                        $release_date = $movie['release_date'] ?? null;
                        $time_ago = $release_date ? 'há '.time_ago(strtotime($release_date)) : 'Em breve';
                        
                        echo '<a href="https://www.themoviedb.org/movie/'.$movie['id'].'" target="_blank" class="list-group-item list-group-item-action notification-movie">';
                        echo '<div class="d-flex align-items-start">';
                        echo '<div class="notification-icon">';
                        echo '<i class="fas fa-film"></i>';
                        echo '</div>';
                        echo '<div class="flex-grow-1">';
                        echo '<h6 class="mb-1">'.htmlspecialchars($movie['title']).'</h6>';
                        echo '<p class="mb-1 small text-muted">'.substr(htmlspecialchars($movie['overview'] ?? 'Detalhes em breve...'), 0, 80).'...</p>';
                        echo '<small class="text-muted">';
                        echo '<i class="far fa-clock mr-1"></i> '.$time_ago;
                        echo '</small>';
                        echo '</div>';
                        echo '</div>';
                        echo '</a>';
                    }
                }
                
                // Notificações de séries no ar
                if(isset($tv_news['results'])) {
                    foreach(array_slice($tv_news['results'], 0, 1) as $tv) {
                        $air_date = $tv['first_air_date'] ?? null;
                        $time_ago = $air_date ? 'há '.time_ago(strtotime($air_date)) : 'Em breve';
                        
                        echo '<a href="https://www.themoviedb.org/tv/'.$tv['id'].'" target="_blank" class="list-group-item list-group-item-action notification-tv">';
                        echo '<div class="d-flex align-items-start">';
                        echo '<div class="notification-icon">';
                        echo '<i class="fas fa-tv"></i>';
                        echo '</div>';
                        echo '<div class="flex-grow-1">';
                        echo '<h6 class="mb-1">'.htmlspecialchars($tv['name']).'</h6>';
                        echo '<p class="mb-1 small text-muted">'.substr(htmlspecialchars($tv['overview'] ?? 'Detalhes em breve...'), 0, 80).'...</p>';
                        echo '<small class="text-muted">';
                        echo '<i class="far fa-clock mr-1"></i> '.$time_ago;
                        echo '</small>';
                        echo '</div>';
                        echo '</div>';
                        echo '</a>';
                    }
                }
                
                if(!isset($movie_news['results']) && !isset($tv_news['results'])) {
                    echo '<div class="list-group-item">';
                    echo '<div class="text-center py-3">';
                    echo '<i class="fas fa-info-circle mr-2"></i>';
                    echo 'Nenhuma notificação recente';
                    echo '</div>';
                    echo '</div>';
                }
                ?>
              </div>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Últimos Adicionados -->
      <div class="row mt-4">
        <div class="col-12">
          <div class="card">
            <div class="card-header">
              <h5 class="mb-0"><i class="fas fa-clock mr-2"></i>Últimos Adicionados</h5>
            </div>
            <div class="card-body p-0">
              <?php foreach ($ultimos_adicionados as $item): ?>
              <div class="movie-item">
                <img src="<?php echo $item['imagem']; ?>" alt="<?php echo htmlspecialchars($item['titulo']); ?>" onerror="this.src='https://via.placeholder.com/60x90?text=No+Image'">
                <div class="movie-info">
                  <div class="title"><?php echo htmlspecialchars($item['titulo']); ?></div>
                  <div class="meta">
                    <span class="badge <?php echo $item['tipo'] === 'filme' ? 'badge-movie' : 'badge-serie'; ?>">
                      <?php echo ucfirst($item['tipo']); ?>
                    </span>
                    <span class="ml-2">Adicionado em: <?php echo date('d/m/Y', strtotime($item['data'])); ?></span>
                  </div>
                </div>
                <div>
                  <a href="<?php echo $item['tipo'] === 'filme' ? 'f.php?tmdbID='.$item['id'] : 's.php?tmdbID='.$item['id']; ?>" class="btn btn-sm btn-outline-secondary">
                    <i class="fas fa-edit"></i>
                  </a>
                  <button class="btn btn-sm btn-outline-danger ml-1">
                    <i class="fas fa-trash"></i>
                  </button>
                </div>
              </div>
              <?php endforeach; ?>
            </div>
            <div class="card-footer text-right">
              <a href="#" class="btn btn-sm btn-primary">Ver Todos</a>
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

    // Gráfico de atividade
    const ctx = document.getElementById('activityChart').getContext('2d');
    const activityChart = new Chart(ctx, {
      type: 'line',
      data: {
        labels: ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul'],
        datasets: [
          {
            label: 'Filmes Adicionados',
            data: [45, 78, 66, 89, 56, 87, 90],
            borderColor: '#4cc9f0',
            backgroundColor: 'rgba(76, 201, 240, 0.1)',
            tension: 0.3,
            fill: true
          },
          {
            label: 'Séries Adicionadas',
            data: [20, 35, 40, 30, 45, 35, 40],
            borderColor: '#4361ee',
            backgroundColor: 'rgba(67, 97, 238, 0.1)',
            tension: 0.3,
            fill: true
          }
        ]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            position: 'top',
            labels: {
              color: '#e6e6e6'
            }
          }
        },
        scales: {
          y: {
            beginAtZero: true,
            grid: {
              color: 'rgba(255, 255, 255, 0.1)'
            },
            ticks: {
              color: '#e6e6e6'
            }
          },
          x: {
            grid: {
              color: 'rgba(255, 255, 255, 0.1)'
            },
            ticks: {
              color: '#e6e6e6'
            }
          }
        }
      }
    });
  </script>
</body>
</html>