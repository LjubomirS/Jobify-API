<?php

namespace App\Controller;

use App\Entity\JobApplication;
use App\Repository\ApplicantRepository;
use App\Repository\JobApplicationRepository;
use App\Repository\JobRepository;
use Nelmio\ApiDocBundle\Annotation\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use OpenApi\Attributes as OA;

#[OA\Tag(name: "JobApplication")]
class JobApplicationController extends AbstractController
{
    #[Route(path: "/api/v1/jobs_applications", methods: "POST")]
    #[OA\Post(description: "Create Job Application.")]
    #[Security(name: "Bearer")]
    #[OA\RequestBody(
        description: "Json to create the job application",
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "applicant", type: "string", example: "018733dd-d61e-701b-a6fa-7c5254335750"),
                new OA\Property(property: "job", type: "string", example: "018733fc-476c-751d-a49d-4ccd43a11ce9")
            ]
        )
    )]
    #[OA\Response(
        response: 201,
        description: 'Returns the ID of the JobApplication',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "statusCode", type: "int", example: 201),
                new OA\Property(property: "message", type: "string", example: "JobApplication created"),
                new OA\Property(property: "data", type: "object")
            ]
        )
    )]
    #[OA\Response(
        response: 400,
        description: 'Invalid arguments',
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: "statusCode", type: "int", example: 400),
                new OA\Property(property: "message", type: "string", example: "Invalid arguments"),
                new OA\Property(property: "data", type: "object")
            ]
        )
    )]
    public function create(
        JobRepository $jobRepository,
        ApplicantRepository $applicantRepository,
        JobApplicationRepository $applicationRepository,
        Request $request,
        ValidatorInterface $validator
    ): Response
    {
        $jsonParams = json_decode($request->getContent(), true);

        $job = $jobRepository->find($jsonParams['job']);
        $applicant = $applicantRepository->find($jsonParams['applicant']);

        if ($job === null) {
            return $this->jsonResponse("Job not found", ['id' => $jsonParams['job']], 404);
        }

        if ($applicant === null) {
            return $this->jsonResponse("Applicant not found", ['id' => $jsonParams['applicant']], 404);
        }

        $jobApplication = new JobApplication();
        $jobApplication->setJob($job);
        $jobApplication->setApplicant($applicant);
        $jobApplication->setCreatedAt(new \DateTimeImmutable());

        $violations = $validator->validate($jobApplication);

        if(count($violations) === 0){
            $applicationRepository->save($jobApplication, true);

            return $this->jsonResponse("Job application created", [
                'id' => (string)$jobApplication->getId(),
            ], 201);
        }

        $errorData = [];
        /** @var ConstraintViolationInterface $violation */
        foreach ($violations as $violation){
            $errorData[$violation->getPropertyPath()][] = $violation->getMessage();
        }

        return $this->jsonResponse("Invalid input", $errorData, 400);
    }

    #[Route(path: "/api/v1/jobs_applications", methods: "GET")]
    #[OA\Get(description: "Return all Job Applications.")]
    #[Security(name: "Bearer")]
    public function findAll(JobApplicationRepository $repository): Response
    {
        $jobsApplications = $repository->findAll();

        $response = [];
        foreach ($jobsApplications as $jobsApplication) {
            $response[] = $jobsApplication->toArray();
        }

        return $this->jsonResponse("List of job applications", $response);
    }

    #[Route(path: "/api/v1/jobs_applications/{id}", methods: "GET")]
    #[OA\Get(description: "Return Job Application by ID.")]
    #[Security(name: "Bearer")]
    public function findById(JobApplicationRepository $repository, string $id): Response
    {
        $jobApplication = $repository->find($id);

        if ($jobApplication === null) {
            return $this->jsonResponse("Job application not found", ['id' => $id], 404);
        }

        return $this->jsonResponse("Job application by ID", [
            $jobApplication->toArray()
        ]);
    }

    #[Route('/api/v1/jobs_applications/filter-by-applicant/{applicantId}', methods: ['GET'])]
    #[OA\Get(description: "Filter Job Applications by applicant.")]
    #[Security(name: "Bearer")]
    public function filterByApplicant(ApplicantRepository $applicantRepository, JobApplicationRepository $repository, string $applicantId): Response
    {
        $applicant = $applicantRepository->find($applicantId);

        if ($applicant === null) {
            return $this->jsonResponse("Applicant not found", ['id' => $applicantId], 404);
        }

        $jobs = $repository->findBy(['applicant' => $applicant]);

        $response = [];
        foreach ($jobs as $job) {
            $response[] = $job->toArray();
        }

        return $this->jsonResponse("List of applications for job", $response);
    }

    #[Route('/api/v1/jobs_applications/filter-by-job/{jobId}', methods: ['GET'])]
    #[OA\Get(description: "Filter Job Applications by job.")]
    #[Security(name: "Bearer")]
    public function filterByJob(JobRepository $jobRepository, JobApplicationRepository $repository, string $jobId): Response
    {
        $job = $jobRepository->find($jobId);

        if ($job === null) {
            return $this->jsonResponse("Job not found", ['id' => $jobId], 404);
        }

        $applicants = $repository->findBy(['job' => $job]);

        $response = [];
        foreach ($applicants as $applicant) {
            $response[] = $applicant->toArray();
        }

        return $this->jsonResponse("List of jobs applied by applicant", $response);
    }

    #[Route(path: "/api/v1/jobs_applications/{id}", methods: "DELETE")]
    #[OA\Delete(description: "Delete Job Application by ID.")]
    #[Security(name: "Bearer")]
    public function remove(JobApplicationRepository $repository, string $id): Response
    {
        $jobApplication = $repository->find($id);

        if ($jobApplication === null) {
            return $this->jsonResponse("Job application not found", ['id' => $id], 404);
        }

        $repository->remove($jobApplication, true);

        return $this->jsonResponse("Job application deleted", [
            $jobApplication->toArray()
        ]);
    }

    private function jsonResponse(string $message, array $data, int $statusCode = 200): JsonResponse
    {
        return $this->json([
            "statusCode" => $statusCode,
            "message" => $message,
            "data" => $data
        ], $statusCode);
    }
}