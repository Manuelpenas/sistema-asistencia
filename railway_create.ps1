$headers = @{
    "Authorization" = "Bearer 8341bb4c-512a-4960-a402-74283fc69835"
    "Content-Type" = "application/json"
    "Accept" = "application/json"
}

$body = @{
    query = 'mutation { projectCreate(input: { name: "sistema-asistencia", description: "Sistema PHP" }) { project { id name } }'
} | ConvertTo-Json

try {
    $response = Invoke-RestMethod -Uri "https://backing.railway.app/graphql" -Method Post -Headers $headers -Body $body
    $response | ConvertTo-Json -Depth 10
} catch {
    $_.Exception.Response
}
