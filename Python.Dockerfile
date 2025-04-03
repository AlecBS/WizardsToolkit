FROM python:3.11.4-alpine3.18

WORKDIR /app

COPY ./python/pythonRequired.txt /app/

RUN pip install --no-cache-dir -r pythonRequired.txt
RUN pip install --no-cache-dir flask-cors gunicorn

COPY ./python/app.py /app/

# Use Gunicorn to serve the Flask app
CMD ["gunicorn", "-w", "4", "-b", "0.0.0.0:5000", "app:app"]

# if add ports to docker-compose.yml Python section can use below; but not good for security
# call like: http://127.0.0.1:5001/?pw=SomePW&step=4th
