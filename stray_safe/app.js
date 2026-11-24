document.addEventListener('DOMContentLoaded', () => {
  const sightingForm = document.getElementById('sightingForm');
  if (sightingForm) {
    const toggleMapsBtn = document.getElementById('toggleMapsBtn');
    const mapsInput = document.getElementById('maps_link');
    const photosInput = document.getElementById('photos');
    const fileCountText = document.getElementById('fileCountText');

    toggleMapsBtn.addEventListener('click', () => {
      mapsInput.style.display = mapsInput.style.display === 'block' ? 'none' : 'block';
    });

    photosInput.addEventListener('change', () => {
      const count = photosInput.files.length;
      fileCountText.textContent = count > 0 ? `${count} photo(s) selected` : '';
    });

    sightingForm.addEventListener('submit', async (e) => {
      e.preventDefault();
      const formData = new FormData(sightingForm);

      try {
        const response = await fetch('create_post.php', {
          method: 'POST',
          body: formData
        });
        const rawText = await response.text();
        const result = JSON.parse(rawText);

        if (result.success) {
          alert('Sighting published!');
          window.location.href = 'community.php';
        } else {
          alert('Error: ' + result.message);
        }
      } catch (err) {
        console.error('Submission error:', err);
        alert('An error occurred while publishing the post.');
      }
    });
  }

  const postsContainer = document.getElementById('postsContainer');
  const statusFilter = document.getElementById('statusFilter');

  if (postsContainer) {
    async function loadPosts(status = 'All') {
      try {
        const response = await fetch(`fetch_posts.php?status=${encodeURIComponent(status)}`);
        const posts = await response.json();
        renderPosts(posts);
      } catch (err) {
        console.error('Error loading posts:', err);
      }
    }

    function renderPosts(posts) {
      postsContainer.innerHTML = '';

      if (posts.length === 0) {
        postsContainer.innerHTML = '<p style="text-align:center; color:#5c6660; padding: 2rem;">No sightings posted yet for this category.</p>';
        return;
      }

      posts.forEach(post => {
        const images = post.images || [];
        let imgHtml = '';

        if (images.length === 0) {
          imgHtml = `
            <div class="image-wrapper"><img src="https://placehold.co/150x120/e2e8f0/64748b?text=No+Photo" alt="Placeholder" /></div>
            <div class="image-wrapper"><img src="https://placehold.co/150x120/e2e8f0/64748b?text=No+Photo" alt="Placeholder" /></div>
          `;
        } else if (images.length === 1) {
          imgHtml = `
            <div class="image-wrapper"><img src="${escapeHTML(images[0])}" alt="Sighting photo" /></div>
            <div class="image-wrapper"><img src="https://placehold.co/150x120/e2e8f0/64748b?text=No+Photo" alt="Placeholder" /></div>
          `;
        } else if (images.length === 2) {
          imgHtml = `
            <div class="image-wrapper"><img src="${escapeHTML(images[0])}" alt="Sighting photo 1" /></div>
            <div class="image-wrapper"><img src="${escapeHTML(images[1])}" alt="Sighting photo 2" /></div>
          `;
        } else {
          const extraCount = images.length - 2;
          imgHtml = `
            <div class="image-wrapper"><img src="${escapeHTML(images[0])}" alt="Sighting photo 1" /></div>
            <div class="image-wrapper">
              <img src="${escapeHTML(images[1])}" alt="Sighting photo 2" />
              <div class="more-photos-overlay">+${extraCount}</div>
            </div>
          `;
        }

        const mapHref = post.maps_link ? post.maps_link : `https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(post.location_text)}`;

        const card = document.createElement('article');
        card.className = 'post-card';
        card.innerHTML = `
          <button class="close-post-btn" title="Hide post" onclick="this.closest('.post-card').remove()">X</button>
          <div class="post-grid">
            <div class="post-left">
              <div class="user-header">
                <img src="https://placehold.co/60x60/a7f3d0/146c53?text=${escapeHTML(post.username.slice(-3))}" alt="Avatar" class="user-avatar" />
                <div class="user-meta">
                  <h4>${escapeHTML(post.username)}</h4>
                  <span>${escapeHTML(post.formatted_date)}</span>
                </div>
              </div>
              <div class="post-images">
                ${imgHtml}
              </div>
            </div>

            <div class="post-details">
              <h3>${escapeHTML(post.location_text)}</h3>
              <a href="${escapeHTML(mapHref)}" target="_blank" class="view-location">View Specific Location</a>
              <p class="post-desc">${escapeHTML(post.description)}</p>
              <p class="post-condition">Condition: ${escapeHTML(post.animal_condition)}</p>
            </div>
          </div>

          <div class="post-footer">
            <button class="action-btn">
              <i class="fa-solid fa-heart" style="color: #c53030;"></i> <span>${post.likes_count}</span>
            </button>
            <button class="action-btn">
              <i class="fa-regular fa-comment-dots"></i> <span>${post.comments_count}</span>
            </button>
            <button class="action-btn">
              <i class="fa-solid fa-share"></i>
            </button>
            <button class="action-btn">
              <i class="fa-regular fa-bookmark"></i>
            </button>
          </div>
        `;
        postsContainer.appendChild(card);
      });
    }

    if (statusFilter) {
      statusFilter.addEventListener('change', () => {
        loadPosts(statusFilter.value);
      });
    }

    loadPosts();
  }

  function escapeHTML(str) {
    const div = document.createElement('div');
    div.textContent = str ?? '';
    return div.innerHTML;
  }
});