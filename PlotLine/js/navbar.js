document.addEventListener('DOMContentLoaded', () => {
    const searchWrapper = document.getElementById('searchWrapper');
    const searchInput = document.getElementById('searchInput');
    const badge = document.getElementById('searchTypeBadge');
    const searchResults = document.getElementById('searchResults');
    const searchBtn = document.getElementById('searchBtn');

    // 1. Toggle Active State
    searchBtn.addEventListener('click', () => {
        searchWrapper.classList.add('active');
        searchInput.focus();
    });

    // 2. Input Logic: Badge Toggle + Fetch Results
    searchInput.addEventListener('input', (e) => {
        const value = e.target.value;

        if (value.length > 0) {
            badge.classList.remove('d-none');
            if (value.startsWith('@')) {
                badge.textContent = "User";
                badge.style.backgroundColor = "#d1e7ff";
                badge.style.color = "#004d99";
            } else {
                badge.textContent = "Novel";
                badge.style.backgroundColor = "#e2e3e5";
                badge.style.color = "#444";
            }
        } else {
            badge.classList.add('d-none');
            searchResults.classList.add('d-none');
            return;
        }

        if (value.length >= 2) {
            fetch(`_actions/search.php?q=${encodeURIComponent(value)}`)
                .then(res => res.json())
                .then(data => {
                    searchResults.innerHTML = '';
                    if (data.results && data.results.length > 0) {
                        data.results.forEach(item => {
                            // --- LOGIC FIXES START HERE ---
                            
                            let isUser = data.type === 'user';
                            let link = isUser ? `profile.php?id=${item.id}` : `view-story.php?id=${item.id}`;
                            let title = isUser ? item.name : item.title;
                            
                            // Updated Sub-text logic
                            // For novels, it now shows "by Author Name • Status"
                            let sub = isUser 
                                ? `@${item.name.toLowerCase().replace(/\s/g, '')}` 
                                : `by ${item.author_name} • <span class="text-capitalize text-primary">${item.status}</span>`;
                            
                            // Match your DB column 'cover_image'
                            let imgSource = isUser ? item.profile_image : item.cover_image;

                            let imageHTML = '';
                            if (imgSource) {
                                imageHTML = `<img src="${imgSource}" class="search-result-img" style="width: 40px; height: 50px; border-radius: 4px; object-fit: cover; margin-right: 12px;">`;
                            } else {
                                const firstLetter = title ? title.charAt(0).toUpperCase() : '?';
                                imageHTML = `
                                    <div class="search-result-img d-flex align-items-center justify-content-center text-white fw-bold" 
                                         style="width: 40px; height: 50px; border-radius: 4px; margin-right: 12px; font-size: 1rem; background-color: #a3d4ff; color: #1a2a40;">
                                        ${firstLetter}
                                    </div>`;
                            }

                            searchResults.innerHTML += `
                                <a href="${link}" class="search-result-item d-flex align-items-center p-2 text-decoration-none border-bottom">
                                    ${imageHTML}
                                    <div style="overflow: hidden;">
                                        <div class="fw-bold small text-dark text-truncate">${title}</div>
                                        <div class="text-muted text-truncate" style="font-size: 0.75rem;">${sub}</div>
                                    </div>
                                </a>
                            `;
                            // --- LOGIC FIXES END HERE ---
                        });
                        searchResults.classList.remove('d-none');
                    } else {
                        searchResults.innerHTML = '<div class="p-3 small text-muted text-center">No results found</div>';
                        searchResults.classList.remove('d-none');
                    }
                })
                .catch(err => console.error("Search Error:", err));
        }
    });

    // 3. Close when clicking outside
    document.addEventListener('click', (e) => {
        if (!searchWrapper.contains(e.target)) {
            if (searchInput.value.length === 0) {
                searchWrapper.classList.remove('active');
                badge.classList.add('d-none');
            }
            searchResults.classList.add('d-none');
        }
    });
});


