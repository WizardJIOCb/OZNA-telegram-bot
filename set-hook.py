import requests

api_token = '7902640773:AAF8Vatss8SpAIqUTag9BL6XMQCLMCBrWlQ'
webhook_url = 'https://api.ladyzenko.pro/tg/ozna/hook.php'

response = requests.post(
    f'https://api.telegram.org/bot{api_token}/setWebhook',
    data={'url': webhook_url}
)

if response.status_code == 200:
    print('Webhook has been set successfully!')
else:
    print('Failed to set webhook. Please check your API token and URL.')
print(response.status_code)
print(response)
