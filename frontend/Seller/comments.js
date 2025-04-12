function toggleReplyForm(button) {
    // Close all other open forms first
    const allForms = document.querySelectorAll('.reply-form.active');
    allForms.forEach(form => {
      if (form !== button.nextElementSibling) {
        form.classList.remove('active');
      }
    });
    
    const form = button.nextElementSibling;
    form.classList.toggle('active');
    
    if (form.classList.contains('active')) {
      form.querySelector('textarea').focus();
    }
  }
  
  function submitReply(button) {
    const form = button.parentElement;
    const textarea = form.querySelector('textarea');
    const content = textarea.value.trim();
    
    if (!content) {
      alert('Please enter a reply');
      return;
    }
    
    const repliesContainer = form.nextElementSibling;
    const replyElement = document.createElement('div');
    replyElement.className = 'reply';
    
    // Get current user info (you should replace this with actual user data)
    const currentUser = {
      name: 'Current User',
      avatar: 'user_avatar.jpg'
    };
    
    replyElement.innerHTML = `
      <i class="fas fa-times delete-reply" onclick="deleteReply(this)"></i>
      <h5>${currentUser.name}</h5>
      <p>${content}</p>
    `;
    
    repliesContainer.appendChild(replyElement);
    textarea.value = '';
    form.classList.remove('active');
  }
  
  function deleteReply(deleteButton) {
    const reply = deleteButton.parentElement;
    reply.style.opacity = '0';
    reply.style.transform = 'translateX(20px)';
    
    setTimeout(() => {
      reply.remove();
    }, 300);
  }