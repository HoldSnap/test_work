<template>
  <div>
    <h1>Комментарии к изображению</h1>
    <form @submit.prevent="addComment">
      <input v-model="name" type="text" placeholder="Имя" required />
      <textarea v-model="text" placeholder="Текст комментария" required></textarea>
      
      <!-- reCAPTCHA -->
      <div class="g-recaptcha" :data-sitekey="siteKey"></div>
      
      <button type="submit">Добавить комментарий</button>
    </form>

    <h2>Список комментариев</h2>
    <ul>
      <li v-for="comment in comments" :key="comment.id">
        <strong>{{ comment.name }}</strong> ({{ comment.created_at }}):
        <p>{{ comment.text }}</p>
        <button @click="deleteComment(comment.id)">Удалить</button>
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

    onMounted(() => {
      fetchComments();
      // Загружаем reCAPTCHA
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
      siteKey,
    };
  },
};
</script>
