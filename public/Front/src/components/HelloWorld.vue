<template>
  <div class="comments">
    <img src="../assets/image.png" alt="Кошечка" class="comments__cat-image" />
    <h1 class="comments__title">Напиши очень смешной коммент</h1>
    <form class="comments__form" @submit.prevent="addComment">
      <input v-model="name" class="comments__input" type="text" placeholder="Имя" required />
      <textarea v-model="text" class="comments__textarea" placeholder="Текст комментария" required></textarea>
      
      <div class="g-recaptcha" :data-sitekey="siteKey"></div>
      
      <button type="submit" class="comments__button">Добавить комментарий</button>
    </form>

    <h2 class="comments__title">Список комментариев</h2>
    <ul class="comments__list">
      <li v-for="comment in comments" :key="comment.id" class="comments__item">
        <strong class="comments__item-name">{{ comment.name }}</strong> ({{ comment.created_at }}):
        <p>{{ comment.text }}</p>
        <button @click="deleteComment(comment.id)" class="comments__delete-button">Удалить</button>
      </li>
    </ul>
  </div>
</template>


<script>
import { ref, onMounted } from 'vue';
import axios from 'axios';

export default {
  setup() {
    const name = ref('');
    const text = ref('');
    const comments = ref([]);
    const siteKey = '6LfrUUsqAAAAAGefsEzjDOjDEKJ6bU3pAeNDPmGS';  

    const fetchComments = async () => {
      try {
        const response = await axios.get('http://localhost:8888/comments');
        comments.value = response.data;
      } catch (error) {
        console.error('Ошибка при получении комментариев:', error);
      }
    };

    const addComment = async () => {
      const recaptchaResponse = grecaptcha.getResponse(); 
      if (!recaptchaResponse) {
        alert('Пожалуйста, подтвердите, что вы не робот');
        return;
      }

      try {
        await axios.post('http://localhost:8888/comments', {
          name: name.value,
          text: text.value,
          recaptcha: recaptchaResponse,  
        });
        name.value = '';
        text.value = '';
        grecaptcha.reset(); 
        fetchComments();
      } catch (error) {
        console.error('Ошибка при добавлении комментария:', error);
      }
    };

    const deleteComment = async (id) => {
      try {
        await axios.delete(`http://localhost:8888/comments/${id}`);
        fetchComments();
      } catch (error) {
        console.error('Ошибка при удалении комментария:', error);
      }
    };

    onMounted(() => {
      fetchComments();
      const script = document.createElement('script');
      script.src = 'https://www.google.com/recaptcha/api.js';
      script.async = true;
      document.head.appendChild(script);
    });

    return {
      name,
      text,
      comments,
      addComment,
      deleteComment, 
      siteKey,
    };
  },
};

</script>
<style scoped>
  .comments {
    font-family: Arial, sans-serif;
    background: linear-gradient(135deg, #f9a825, #ff6f00);
    color: #333;
    padding: 20px;
  }

  .comments__cat-image {
    display: block;
    margin: 0 auto;
    width: 100%;
    max-width: 800px;
    border-radius: 10px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
  }

  .comments__title {
    margin-top: 20px;
    color: #ffffff;
    text-align: center;
    font-weight: 800;
    margin-bottom: 20px;
  }

  .comments__form {
    max-width: 600px;
    margin: 0 auto;
    padding: 20px;
    background-color: #ffffff;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    border-radius: 8px;
  }

  .comments__input, .comments__textarea {
    width: 100%;
    padding: 10px;
    margin-bottom: 15px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 16px;
  }

  .comments__button {
    display: block;
    width: 100%;
    padding: 10px;
    background-color: #ff6f00;
    color: white;
    font-size: 16px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    transition: background-color 0.3s ease;
  }

  .comments__button:hover {
    background-color: #f57c00;
  }

  .comments__list {
    padding: 0;
    margin: 0 auto;
    max-width: 600px;
  }

  .comments__item {
    background-color: #fff3e0;
    margin-bottom: 15px;
    padding: 15px;
    border-radius: 8px;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
  }

  .comments__item-name {
    color: #ff6f00;
  }

  .comments__delete-button {
    background-color: #e64a19;
    padding: 5px 10px;
    font-size: 14px;
    margin-top: 10px;
    border: none;
    color: white;
    border-radius: 4px;
    cursor: pointer;
  }

  .comments__delete-button:hover {
    background-color: #d84315;
  }

  .g-recaptcha {
    margin-bottom: 15px;
  }
</style>
