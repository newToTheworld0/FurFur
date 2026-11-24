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
    postsContainer.addEventListener('click', async (e) => {
        const likeButton = e.target.closest('.like-btn');

        if (!likeButton) return;

        const postId = likeButton.dataset.postId;

        const formData = new FormData();
        formData.append('post_id', postId);

        try {
            const response = await fetch('toggle_like.php', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            if (!result.success) {
                alert(result.message);
                return;
            }

            const heartIcon = likeButton.querySelector('i');

            heartIcon.className = result.liked
                ? 'fa-solid fa-heart'
                : 'fa-regular fa-heart';

            heartIcon.style.color = result.liked ? '#c53030' : 'inherit';

            likeButton.querySelector('span').textContent = result.likes_count;

        } catch (err) {
            console.error('Like request error:', err);
            alert('Something went wrong while liking this post.');
        }
    });


    // ADDED: Retrieve and display comments for a selected post.
    async function loadComments(postId) {
        const postCard = postsContainer.querySelector(
            `.post-card:has(.comment-btn[data-post-id="${postId}"])`
        );

        if (!postCard) return;

        const commentsList = postCard.querySelector('.comments-list');
        const commentCount = postCard.querySelector('.comment-btn span');

        try {
            const response = await fetch(
                `fetch_comments.php?post_id=${encodeURIComponent(postId)}`
            );

            const result = await response.json();

            if (!result.success) {
                alert(result.message);
                return;
            }

            commentsList.innerHTML = '';

            if (result.comments.length === 0) {
                const emptyMessage = document.createElement('p');
                emptyMessage.textContent = 'No comments yet. Be the first to comment!';
                commentsList.appendChild(emptyMessage);
            } else {
                result.comments.forEach(comment => {
                    const commentItem = document.createElement('div');
                    commentItem.className = 'comment-item';
                    commentItem.dataset.commentId = comment.id;

                    // ADDED: Header keeps username on the left and delete button on the right.
                    const commentHeader = document.createElement('div');
                    commentHeader.className = 'comment-header';

                    const username = document.createElement('strong');
                    username.textContent = comment.username;

                    commentHeader.appendChild(username);

                    // ADDED: Only the comment owner can see the delete button.
                    if (comment.is_owner) {
                        const deleteButton = document.createElement('button');
                        deleteButton.type = 'button';
                        deleteButton.className = 'delete-comment-btn';
                        deleteButton.dataset.commentId = comment.id;
                        deleteButton.textContent = 'Delete';

                        commentHeader.appendChild(deleteButton);
                    }

                    // ADDED: Comment text appears below the username.
                    const commentText = document.createElement('p');
                    commentText.textContent = comment.comment;

                    // ADDED: Date and time appear at the bottom.
                    const commentDate = document.createElement('small');
                    commentDate.textContent = comment.formatted_date;

                    commentItem.appendChild(commentHeader);
                    commentItem.appendChild(commentText);
                    commentItem.appendChild(commentDate);

                    commentsList.appendChild(commentItem);
                });

            }

            commentCount.textContent = result.comments_count;

        } catch (err) {
            console.error('Error loading comments:', err);
            alert('Unable to load comments.');
        }
    }

    // ADDED: Open or close the comments section when clicked.
    postsContainer.addEventListener('click', async (e) => {
        const commentButton = e.target.closest('.comment-btn');

        if (!commentButton) return;

        const postCard = commentButton.closest('.post-card');
        const commentsSection = postCard.querySelector('.comments-section');

        commentsSection.hidden = !commentsSection.hidden;

        if (!commentsSection.hidden) {
            await loadComments(commentButton.dataset.postId);
        }
    });

    postsContainer.addEventListener('click', async (e) => {
        const deleteButton = e.target.closest('.delete-comment-btn');

        if (!deleteButton) return;

        const commentItem = deleteButton.closest('.comment-item');
        const postCard = deleteButton.closest('.post-card');

        const commentId = commentItem.dataset.commentId;

        if (!confirm('Are you sure you want to delete this comment?')) {
            return;
        }

        const commentFormData = new FormData();
        commentFormData.append('comment_id', commentId);
        commentFormData.append('action', 'delete');

        try {
            const response = await fetch('comment_process.php', {
                method: 'POST',
                body: commentFormData
            });

            const result = await response.json();

            if (!result.success) {
                alert(result.message);
                return;
            }

            const postId = postCard.querySelector('.comment-btn').dataset.postId;

            // ADDED: Reload comments and update the comment count.
            await loadComments(postId);

        } catch (err) {
            console.error('Comment deletion error:', err);
            alert('Something went wrong while deleting the comment.');
        }
    });

    postsContainer.addEventListener('submit', async (e) => {
        const commentForm = e.target.closest('.comment-form');

        if (!commentForm) return;

        e.preventDefault();

        const postCard = commentForm.closest('.post-card');
        const commentButton = postCard.querySelector('.comment-btn');
        const postId = commentButton.dataset.postId;

        const formData = new FormData(commentForm);
        formData.append('post_id', postId);

        try {
            formData.append('action', 'add');

            const response = await fetch('comment_process.php', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            if (!result.success) {
                alert(result.message);
                return;
            }

            commentForm.reset();

            await loadComments(postId);

        } catch (err) {
            console.error('Comment submission error:', err);
            alert('Something went wrong while posting your comment.');
        }
    });

    // ADDED: Handle sharing individual posts.
    postsContainer.addEventListener('click', async (e) => {
        const shareButton = e.target.closest('.share-btn');

        if (!shareButton) return;

        const postId = shareButton.dataset.postId;
        const postUrl = `${window.location.origin}${window.location.pathname}#post-${postId}`;

        try {
            if (navigator.clipboard && window.isSecureContext) {
                await navigator.clipboard.writeText(postUrl);
                alert('Post link copied! You can now share it.');
            } else {
                // ADDED: Fallback for browsers without clipboard API support.
                const tempInput = document.createElement('textarea');
                tempInput.value = postUrl;
                tempInput.style.position = 'fixed';
                tempInput.style.opacity = '0';

                document.body.appendChild(tempInput);
                tempInput.select();

                const copied = document.execCommand('copy');
                tempInput.remove();

                if (copied) {
                    alert('Post link copied! You can now share it.');
                } else {
                    window.prompt('Copy this post link:', postUrl);
                }
            }

        } catch (error) {
            console.error('Share error:', error);
            window.prompt('Copy this post link:', postUrl);
        }
    });

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
        card.id = `post-${post.id}`;
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
              <button class="action-btn like-btn"
                      data-post-id="${post.id}"
                      type="button">
                  <i class="${post.liked_by_user ? 'fa-solid' : 'fa-regular'} fa-heart"
                     style="color: ${post.liked_by_user ? '#c53030' : 'inherit'};"></i>
                  <span>${post.likes_count}</span>
              </button>
              <button class="action-btn comment-btn"
                      data-post-id="${post.id}"
                      type="button">
                  <i class="fa-regular fa-comment-dots"></i>
                  <span>${post.comments_count}</span>
              </button>
              <button class="action-btn share-btn"
                      data-post-id="${post.id}"
                      type="button">
                  <i class="fa-solid fa-share"></i>
                  <span>Share</span>
              </button>
              <button class="action-btn">
                  <i class="fa-regular fa-bookmark"></i>
              </button>
          </div>

          <div class="comments-section" hidden>
              <div class="comments-list"></div>

              <form class="comment-form">
                  <textarea
                      name="comment"
                      placeholder="Write a comment..."
                      rows="2"
                      maxlength="2000"
                      required
                  ></textarea>

                  <button type="submit" class="btn-primary">
                      Post Comment
                  </button>
              </form>
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