<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Busca de Filmes TMDB</title>
  <link rel="icon" href="../assets/favicon.ico" type="image/x-icon">
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f5f5f5;
            margin: 0;
            padding: 20px;
            color: #333;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        h1 {
            text-align: center;
            color: #01b4e4;
            margin-bottom: 30px;
        }
        
        .search-container {
            display: flex;
            justify-content: center;
            margin-bottom: 30px;
        }
        
        #search-input {
            padding: 12px 15px;
            width: 60%;
            border: 2px solid #01b4e4;
            border-radius: 25px 0 0 25px;
            font-size: 16px;
            outline: none;
        }
        
        #search-button {
            padding: 12px 20px;
            background-color: #01b4e4;
            color: white;
            border: none;
            border-radius: 0 25px 25px 0;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s;
        }
        
        #search-button:hover {
            background-color: #0099c3;
        }
        
        .results-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 25px;
            margin-top: 20px;
        }
        
        .movie-card {
            background-color: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s;
        }
        
        .movie-card:hover {
            transform: translateY(-5px);
        }
        
        .movie-poster {
            width: 100%;
            height: 450px;
            object-fit: cover;
        }
        
        .movie-info {
            padding: 15px;
        }
        
        .movie-title {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 5px;
            color: #01b4e4;
        }
        
        .movie-details {
            font-size: 14px;
            color: #666;
            margin-bottom: 10px;
        }
        
        .movie-overview {
            font-size: 14px;
            line-height: 1.4;
            margin-bottom: 15px;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        
        .download-btn {
            display: block;
            width: 100%;
            padding: 8px 0;
            background-color: #01b4e4;
            color: white;
            text-align: center;
            border-radius: 5px;
            text-decoration: none;
            font-size: 14px;
            transition: background-color 0.3s;
            border: none;
            cursor: pointer;
        }
        
        .download-btn:hover {
            background-color: #0099c3;
        }
        
        .post-container {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.8);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }
        
        .post-content {
            background-color: white;
            padding: 20px;
            border-radius: 10px;
            max-width: 90%;
            max-height: 90%;
            overflow: auto;
            text-align: center;
        }
        
        .post-image {
            max-width: 100%;
            max-height: 80vh;
            border-radius: 5px;
        }
        
        .close-btn {
            position: absolute;
            top: 20px;
            right: 20px;
            color: white;
            font-size: 30px;
            cursor: pointer;
        }
        
        .download-post-btn {
            display: inline-block;
            margin-top: 15px;
            padding: 10px 20px;
            background-color: #01b4e4;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-size: 16px;
        }
        
        .loading {
            display: none;
            text-align: center;
            margin: 20px 0;
        }
        
        .spinner {
            border: 5px solid #f3f3f3;
            border-top: 5px solid #01b4e4;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            animation: spin 1s linear infinite;
            margin: 0 auto;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        @media (max-width: 768px) {
            .results-container {
                grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            }
            
            #search-input {
                width: 70%;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Busca de Filmes TMDB</h1>
        
        <div class="search-container">
            <input type="text" id="search-input" placeholder="Pesquise por filmes...">
            <button id="search-button">Buscar</button>
        </div>
        
        <div class="loading">
            <div class="spinner"></div>
            <p>Carregando filmes...</p>
        </div>
        
        <div class="results-container" id="results-container"></div>
    </div>
    
    <div class="post-container" id="post-container">
        <span class="close-btn" id="close-btn">&times;</span>
        <div class="post-content">
            <canvas id="post-canvas" width="1080" height="1350" style="display: none;"></canvas>
            <img id="post-image" class="post-image" src="" alt="Post do Filme">
            <a href="#" class="download-post-btn" id="download-post-btn">Baixar Post</a>
        </div>
    </div>
    
    <script>
        const API_KEY = 'fa2db16fd76770b1408ef23538da6695';
        const BASE_URL = 'https://api.themoviedb.org/3';
        
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('search-input');
            const searchButton = document.getElementById('search-button');
            const resultsContainer = document.getElementById('results-container');
            const loadingDiv = document.querySelector('.loading');
            const postContainer = document.getElementById('post-container');
            const closeBtn = document.getElementById('close-btn');
            const postImage = document.getElementById('post-image');
            const postCanvas = document.getElementById('post-canvas');
            const downloadPostBtn = document.getElementById('download-post-btn');
            
            // Pesquisar quando o botão é clicado
            searchButton.addEventListener('click', searchMovies);
            
            // Pesquisar quando Enter é pressionado
            searchInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    searchMovies();
                }
            });
            
            // Fechar visualização do post
            closeBtn.addEventListener('click', function() {
                postContainer.style.display = 'none';
            });
            
            function searchMovies() {
                const query = searchInput.value.trim();
                
                if (query === '') {
                    alert('Por favor, digite um termo de busca');
                    return;
                }
                
                loadingDiv.style.display = 'block';
                resultsContainer.innerHTML = '';
                
                fetch(`${BASE_URL}/search/movie?api_key=${API_KEY}&language=pt-BR&query=${encodeURIComponent(query)}`)
                    .then(response => response.json())
                    .then(data => {
                        loadingDiv.style.display = 'none';
                        
                        if (data.results && data.results.length > 0) {
                            displayMovies(data.results);
                        } else {
                            resultsContainer.innerHTML = '<p style="grid-column: 1 / -1; text-align: center;">Nenhum filme encontrado. Tente outro termo de busca.</p>';
                        }
                    })
                    .catch(error => {
                        loadingDiv.style.display = 'none';
                        console.error('Erro:', error);
                        resultsContainer.innerHTML = '<p style="grid-column: 1 / -1; text-align: center;">Ocorreu um erro. Por favor, tente novamente mais tarde.</p>';
                    });
            }
            
            function displayMovies(movies) {
                resultsContainer.innerHTML = '';
                
                movies.forEach(movie => {
                    const movieCard = document.createElement('div');
                    movieCard.className = 'movie-card';
                    
                    const posterPath = movie.poster_path 
                        ? `https://cors-anywhere.herokuapp.com/https://image.tmdb.org/t/p/w500${movie.poster_path}`
                        : 'https://via.placeholder.com/500x750?text=Sem+Poster';
                    
                    movieCard.innerHTML = `
                        <img src="${posterPath}" alt="${movie.title}" class="movie-poster" crossorigin="anonymous">
                        <div class="movie-info">
                            <div class="movie-title">${movie.title}</div>
                            <div class="movie-details">
                                ${movie.release_date ? formatDate(movie.release_date) : 'Data não disponível'} | 
                                Avaliação: ${movie.vote_average ? movie.vote_average.toFixed(1) : 'N/A'}
                            </div>
                            <div class="movie-overview">${movie.overview || 'Sinopse não disponível.'}</div>
                            <button class="download-btn" 
                                data-movie-id="${movie.id}" 
                                data-movie-title="${movie.title}" 
                                data-poster-path="${movie.poster_path}" 
                                data-release-date="${movie.release_date}" 
                                data-vote-average="${movie.vote_average}" 
                                data-overview="${movie.overview}">
                                Criar Post
                            </button>
                        </div>
                    `;
                    
                    resultsContainer.appendChild(movieCard);
                });
                
                // Adicionar eventos aos botões de download
                document.querySelectorAll('.download-btn').forEach(button => {
                    button.addEventListener('click', function() {
                        const movieData = {
                            id: this.getAttribute('data-movie-id'),
                            title: this.getAttribute('data-movie-title'),
                            poster_path: this.getAttribute('data-poster-path'),
                            release_date: this.getAttribute('data-release-date'),
                            vote_average: this.getAttribute('data-vote-average'),
                            overview: this.getAttribute('data-overview')
                        };
                        createMoviePost(movieData);
                    });
                });
            }
            
            function formatDate(dateString) {
                if (!dateString) return 'Data não disponível';
                const options = { year: 'numeric', month: 'long', day: 'numeric' };
                return new Date(dateString).toLocaleDateString('pt-BR', options);
            }
            
            function limitOverview(text, maxLength = 200) {
                if (!text) return 'Sinopse não disponível.';
                if (text.length <= maxLength) return text;
                
                // Corta o texto no último espaço antes do limite
                const trimmed = text.substring(0, maxLength);
                return trimmed.substring(0, trimmed.lastIndexOf(' ')) + '...';
            }
            
            function createMoviePost(movie) {
                loadingDiv.style.display = 'block';
                postContainer.style.display = 'none';
                
                const ctx = postCanvas.getContext('2d');
                
                // Limpar canvas
                ctx.clearRect(0, 0, postCanvas.width, postCanvas.height);
                
                // Criar gradiente de fundo
                const gradient = ctx.createLinearGradient(0, 0, postCanvas.width, postCanvas.height);
                gradient.addColorStop(0, '#000000');
                gradient.addColorStop(1, '#FF0000');
                
                ctx.fillStyle = gradient;
                ctx.fillRect(0, 0, postCanvas.width, postCanvas.height);
                
                // Adicionar título do filme
                ctx.fillStyle = 'white';
                ctx.textAlign = 'center';
                ctx.font = 'bold 72px Arial';
                wrapText(ctx, movie.title, postCanvas.width/2, 100, postCanvas.width - 100, 80);
                
                // Adicionar data de lançamento formatada
                ctx.font = '30px Arial';
                ctx.fillText(
                    `Lançamento: ${formatDate(movie.release_date)}`,
                    postCanvas.width/2, 200
                );
                
                // Adicionar avaliação
                ctx.fillText(
                    `Avaliação: ${movie.vote_average ? parseFloat(movie.vote_average).toFixed(1) : 'N/A'}/10`,
                    postCanvas.width/2, 250
                );
                
                // Adicionar poster do filme (se disponível)
                if (movie.poster_path) {
                    const posterImg = new Image();
                    posterImg.crossOrigin = 'Anonymous';
                    posterImg.src = `https://cors-anywhere.herokuapp.com/https://image.tmdb.org/t/p/w500${movie.poster_path}`;
                    
                    posterImg.onload = function() {
                        // Desenhar poster com borda
                        const posterWidth = 500;
                        const posterHeight = 750;
                        const posterX = (postCanvas.width - posterWidth) / 2;
                        const posterY = 300;
                        
                        // Desenhar borda branca
                        ctx.fillStyle = 'white';
                        ctx.fillRect(posterX - 5, posterY - 5, posterWidth + 10, posterHeight + 10);
                        
                        // Desenhar poster
                        ctx.drawImage(posterImg, posterX, posterY, posterWidth, posterHeight);
                        
                        // Adicionar sinopse limitada abaixo do poster
                        ctx.font = '28px Arial';
                        ctx.textAlign = 'center';
                        const limitedOverview = limitOverview(movie.overview);
                        wrapText(ctx, limitedOverview, postCanvas.width/2, posterY + posterHeight + 50, postCanvas.width - 100, 40);
                        
                        // Adicionar GhostHaunt na parte inferior
                        ctx.font = 'bold 36px Arial';
                        ctx.fillStyle = 'rgba(255, 255, 255, 0.7)';
                        ctx.fillText('GhostHaunt', postCanvas.width/2, postCanvas.height - 50);
                        
                        addTMDBLogo();
                    };
                    
                    posterImg.onerror = function() {
                        // Se a imagem do poster não carregar, adicionar apenas texto
                        ctx.font = '28px Arial';
                        const limitedOverview = limitOverview(movie.overview);
                        wrapText(ctx, limitedOverview, postCanvas.width/2, 300, postCanvas.width - 100, 40);
                        
                        // Adicionar GhostHaunt na parte inferior
                        ctx.font = 'bold 36px Arial';
                        ctx.fillStyle = 'rgba(255, 255, 255, 0.7)';
                        ctx.fillText('GhostHaunt', postCanvas.width/2, postCanvas.height - 50);
                        
                        addTMDBLogo();
                    };
                } else {
                    // Se não houver poster, adicionar apenas texto
                    ctx.font = '28px Arial';
                    const limitedOverview = limitOverview(movie.overview);
                    wrapText(ctx, limitedOverview, postCanvas.width/2, 300, postCanvas.width - 100, 40);
                    
                    // Adicionar GhostHaunt na parte inferior
                    ctx.font = 'bold 36px Arial';
                    ctx.fillStyle = 'rgba(255, 255, 255, 0.7)';
                    ctx.fillText('GhostHaunt', postCanvas.width/2, postCanvas.height - 50);
                    
                    addTMDBLogo();
                }
                
                function addTMDBLogo() {
                    const logoImg = new Image();
                    logoImg.crossOrigin = 'Anonymous';
                    logoImg.src = 'https://cors-anywhere.herokuapp.com/https://www.themoviedb.org/assets/2/v4/logos/v2/blue_square_2-d537fb228cf3ded904ef09b136fe3fec72548ebc1fea3fbbd1ad9e36364db38b.svg';
                    
                    logoImg.onload = function() {
                        ctx.drawImage(logoImg, postCanvas.width - 150, postCanvas.height - 100, 120, 60);
                        finalizePost();
                    };
                    
                    logoImg.onerror = function() {
                        finalizePost();
                    };
                }
                
                function finalizePost() {
                    // Converter canvas para imagem e exibir
                    postImage.src = postCanvas.toDataURL('image/jpeg');
                    postContainer.style.display = 'flex';
                    loadingDiv.style.display = 'none';
                    
                    // Configurar link de download
                    downloadPostBtn.href = postCanvas.toDataURL('image/jpeg');
                    downloadPostBtn.download = `post-${movie.title.toLowerCase().replace(/\s+/g, '-')}.jpg`;
                }
            }
            
            // Função auxiliar para quebrar texto em várias linhas
            function wrapText(context, text, x, y, maxWidth, lineHeight) {
                if (!text) return;
                
                const words = text.split(' ');
                let line = '';
                
                for(let n = 0; n < words.length; n++) {
                    const testLine = line + words[n] + ' ';
                    const metrics = context.measureText(testLine);
                    const testWidth = metrics.width;
                    
                    if (testWidth > maxWidth && n > 0) {
                        context.fillText(line, x, y);
                        line = words[n] + ' ';
                        y += lineHeight;
                    } else {
                        line = testLine;
                    }
                }
                
                context.fillText(line, x, y);
            }
        });
    </script>
</body>
</html>
