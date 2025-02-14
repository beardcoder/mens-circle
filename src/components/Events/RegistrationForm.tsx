import Button from '@/components/ui/Button'
import { Input } from '@/components/ui/Input'
import directus from '@/lib/directus'
import { createItem } from '@directus/sdk'
import type { FunctionComponent } from 'preact'
import { useState } from 'preact/hooks'

const RegistrationForm: FunctionComponent = () => {
  const [formData, setFormData] = useState({ name: '', email: '', message: '' })
  const [submitted, setSubmitted] = useState(false)

  const handleChange = (e: Event) => {
    const target = e.target as HTMLInputElement
    setFormData({ ...formData, [target.name]: target.value })
  }

  const handleSubmit = async (e: SubmitEvent) => {
    e.preventDefault()
    try {
      await directus.request(createItem('contact_requests', { ...formData }))
    } catch (error) {
      console.log(error)
    }
    setSubmitted(true)
  }

  return (
    <div>
      <div>
        {submitted ? (
          <div className="border-2 border-l-8 border-green-500 bg-green-300 p-4 text-center font-semibold dark:border-green-600 dark:bg-green-800">
            Danke für deine Nachricht! Ich melde mich bald bei dir.
          </div>
        ) : (
          <form className="space-y-6" onSubmit={handleSubmit}>
            <div class="flex w-full space-x-4">
              <div class="w-1/2">
                <label htmlFor="first_name" className="sr-only">
                  Name
                </label>
                <Input
                  name="first_name"
                  id="first_name"
                  placeholder="Vorname"
                  value={formData.name}
                  onChange={handleChange}
                  required
                  className="w-full"
                />
              </div>
              <div class="w-1/2">
                <label htmlFor="last_name" className="sr-only">
                  Nachname
                </label>
                <Input
                  name="last_name"
                  id="last_name"
                  placeholder="Nachname"
                  value={formData.name}
                  onChange={handleChange}
                  required
                  className="w-full"
                />
              </div>
            </div>
            <div>
              <label htmlFor="email" className="sr-only">
                E-Mail
              </label>
              <Input
                id="email"
                type="email"
                placeholder="E-Mail"
                name="email"
                value={formData.email}
                onChange={handleChange}
                required
              />
            </div>
            <Button type="submit" className="w-full">
              Anmelden
            </Button>
          </form>
        )}
      </div>
    </div>
  )
}

export default RegistrationForm
