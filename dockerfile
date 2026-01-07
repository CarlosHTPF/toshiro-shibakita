FROM nginx:alpine

RUN rm /etc/nginx/conf.d/default.conf

COPY nginx.conf /etc/nginx/nginx.conf

EXPOSE 4500

# Testa a configuração antes de iniciar
RUN nginx -t

CMD ["nginx", "-g", "daemon off;"]
